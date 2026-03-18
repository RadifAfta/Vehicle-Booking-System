<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PersetujuanController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth', 'activity.log'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::get('/pemesanan/search/kendaraan', [PemesananController::class, 'searchKendaraan'])->name('pemesanan.search.kendaraan');
        Route::get('/pemesanan/search/driver', [PemesananController::class, 'searchDriver'])->name('pemesanan.search.driver');
        Route::get('/pemesanan/search/penyetujui', [PemesananController::class, 'searchPenyetuju'])->name('pemesanan.search.penyetuju');
        Route::post('/pemesanan', [PemesananController::class, 'store'])->name('pemesanan.store');
        Route::post('/pemesanan/{pemesanan}/riwayat', [PemesananController::class, 'storeRiwayat'])->name('pemesanan.riwayat.store');
    });

    Route::middleware('role:penyetujui')->group(function (): void {
        Route::get('/persetujuan', [PersetujuanController::class, 'index'])->name('persetujuan.index');
        Route::patch('/persetujuan/{pemesanan}', [PersetujuanController::class, 'update'])->name('persetujuan.update');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
        Route::get('/laporan/log-persetujuan', [LaporanController::class, 'logPersetujuan'])->name('laporan.log-persetujuan');
        Route::get('/laporan/log-aktivitas', [LaporanController::class, 'logAktivitas'])->name('laporan.log-aktivitas');
    });
});
