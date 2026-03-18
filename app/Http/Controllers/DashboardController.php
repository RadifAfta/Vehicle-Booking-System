<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Pemesanan;
use App\Models\RiwayatPemakaian;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $chartRows = RiwayatPemakaian::query()
            ->join('pemesanan', 'riwayat_pemakaian.pemesanan_id', '=', 'pemesanan.id')
            ->join('kendaraan', 'pemesanan.kendaraan_id', '=', 'kendaraan.id')
            ->select('kendaraan.nama', DB::raw('SUM(riwayat_pemakaian.jarak_tempuh_km) as total_km'))
            ->groupBy('kendaraan.nama')
            ->orderByDesc('total_km')
            ->get();

        return view('dashboard.index', [
            'totalKendaraan' => Kendaraan::count(),
            'totalPemesanan' => Pemesanan::count(),
            'chartLabels' => $chartRows->pluck('nama')->toArray(),
            'chartValues' => $chartRows->pluck('total_km')->map(fn ($value) => (float) $value)->toArray(),
        ]);
    }
}
