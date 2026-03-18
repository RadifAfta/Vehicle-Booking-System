<?php

namespace App\Providers;

use App\Models\Pemesanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $pendingCount = 0;

            if (Auth::check() && Auth::user()->role === 'penyetujui') {
                $userId = Auth::id();

                $pendingCount = Pemesanan::query()
                    ->where(function ($query) use ($userId): void {
                        $query->where(function ($inner) use ($userId): void {
                            $inner->where('atasan_1_id', $userId)
                                ->where('status_pemesanan', 'menunggu_persetujuan');
                        })->orWhere(function ($inner) use ($userId): void {
                            $inner->where('atasan_2_id', $userId)
                                ->where('status_pemesanan', 'disetujui_level_1');
                        });
                    })
                    ->count();
            }

            $view->with('approvalPendingCount', $pendingCount);
        });
    }
}
