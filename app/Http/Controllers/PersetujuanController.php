<?php

namespace App\Http\Controllers;

use App\Models\LogPersetujuan;
use App\Models\Pemesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersetujuanController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        return view('persetujuan.index', [
            'pemesananList' => Pemesanan::query()
                ->with(['admin', 'kendaraan', 'driver', 'atasan1', 'atasan2', 'logPersetujuan'])
                ->where(function ($query) use ($userId) {
                    $query->where('atasan_1_id', $userId)
                        ->orWhere('atasan_2_id', $userId);
                })
                ->latest()
                ->get(),
        ]);
    }

    public function update(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $data = $request->validate([
            'aksi' => ['required', Rule::in(['setuju', 'tolak'])],
            'catatan_tambahan' => ['nullable', 'string'],
        ]);

        $userId = Auth::id();

        DB::transaction(function () use ($pemesanan, $data, $userId): void {
            if ($pemesanan->status_pemesanan === 'ditolak' || $pemesanan->status_pemesanan === 'disetujui_final') {
                return;
            }

            if ($pemesanan->atasan_1_id === $userId && $pemesanan->status_pemesanan === 'menunggu_persetujuan') {
                LogPersetujuan::create([
                    'pemesanan_id' => $pemesanan->id,
                    'penyetujui_id' => $userId,
                    'level' => 1,
                    'aksi' => $data['aksi'],
                    'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
                ]);

                $pemesanan->update([
                    'status_pemesanan' => $data['aksi'] === 'setuju' ? 'disetujui_level_1' : 'ditolak',
                ]);

                return;
            }

            if ($pemesanan->atasan_2_id === $userId && $pemesanan->status_pemesanan === 'disetujui_level_1') {
                LogPersetujuan::create([
                    'pemesanan_id' => $pemesanan->id,
                    'penyetujui_id' => $userId,
                    'level' => 2,
                    'aksi' => $data['aksi'],
                    'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
                ]);

                $pemesanan->update([
                    'status_pemesanan' => $data['aksi'] === 'setuju' ? 'disetujui_final' : 'ditolak',
                ]);
            }
        });

        return back()->with('success', 'Aksi persetujuan berhasil diproses.');
    }
}
