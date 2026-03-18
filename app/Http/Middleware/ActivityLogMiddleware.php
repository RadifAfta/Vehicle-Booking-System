<?php

namespace App\Http\Middleware;

use App\Models\LogAktivitas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user()) {
            return $response;
        }

        if ($request->is('up')) {
            return $response;
        }

        LogAktivitas::create([
            'user_id' => $request->user()->id,
            'aktivitas' => $this->buildActivityLabel($request),
            'method' => $request->method(),
            'route_name' => optional($request->route())->getName(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return $response;
    }

    private function buildActivityLabel(Request $request): string
    {
        $routeName = optional($request->route())->getName();

        if (! $routeName) {
            return 'Mengakses halaman '.$request->path();
        }

        return match ($routeName) {
            'dashboard' => 'Membuka dashboard',
            'pemesanan.index' => 'Membuka halaman pemesanan kendaraan',
            'pemesanan.store' => 'Menyimpan pemesanan kendaraan baru',
            'pemesanan.riwayat.store' => 'Menyimpan riwayat pemakaian kendaraan',
            'persetujuan.index' => 'Membuka halaman persetujuan pemesanan',
            'persetujuan.update' => $request->input('aksi') === 'tolak'
                ? 'Menolak pengajuan pemesanan kendaraan'
                : 'Menyetujui pengajuan pemesanan kendaraan',
            'laporan.index' => 'Membuka laporan periodik pemesanan',
            'laporan.export' => 'Export laporan periodik pemesanan ke Excel',
            'laporan.log-persetujuan' => 'Membuka log persetujuan',
            'laporan.log-aktivitas' => 'Membuka log aktivitas user',
            'logout' => 'Logout dari aplikasi',
            default => 'Melakukan aktivitas pada halaman '.str_replace('.', ' ', $routeName),
        };
    }
}
