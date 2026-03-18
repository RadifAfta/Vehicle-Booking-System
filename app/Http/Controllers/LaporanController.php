<?php

namespace App\Http\Controllers;

use App\Exports\PemesananExport;
use App\Models\LogAktivitas;
use App\Models\LogPersetujuan;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Pemesanan::query()->with(['admin', 'kendaraan', 'driver', 'atasan1', 'atasan2']);

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_mulai', [$startDate, $endDate]);
        }

        return view('laporan.index', [
            'pemesananList' => $query->latest()->get(),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $fileName = 'laporan-pemesanan-kendaraan-'.now()->format('YmdHis').'.xlsx';

        return Excel::download(new PemesananExport($data['start_date'] ?? null, $data['end_date'] ?? null), $fileName);
    }

    public function logPersetujuan(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = LogPersetujuan::query()
            ->with(['pemesanan.admin', 'pemesanan.kendaraan', 'penyetujui'])
            ->latest();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        }

        return view('laporan.log-persetujuan', [
            'logList' => $query->get(),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function logAktivitas(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $userId = $request->query('user_id');

        $query = LogAktivitas::query()->with('user')->latest();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return view('laporan.log-aktivitas', [
            'logList' => $query->get(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userId' => $userId,
            'users' => User::orderBy('nama')->get(['id', 'nama']),
        ]);
    }
}
