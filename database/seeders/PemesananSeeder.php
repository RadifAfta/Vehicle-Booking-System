<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\LogPersetujuan;
use App\Models\Pemesanan;
use App\Models\RiwayatPemakaian;
use App\Models\User;
use Illuminate\Database\Seeder;

class PemesananSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->value('id');
        $approver1Id = User::where('email', 'approver1@booking.test')->value('id');
        $approver2Id = User::where('email', 'approver2@booking.test')->value('id');
        $kendaraanId = Kendaraan::query()->value('id');
        $driverId = Driver::query()->value('id');

        if (! $adminId || ! $approver1Id || ! $approver2Id || ! $kendaraanId || ! $driverId) {
            return;
        }

        $pemesanan = Pemesanan::updateOrCreate(
            ['catatan' => 'Sample pemesanan seed'],
            [
                'admin_id' => $adminId,
                'kendaraan_id' => $kendaraanId,
                'driver_id' => $driverId,
                'atasan_1_id' => $approver1Id,
                'atasan_2_id' => $approver2Id,
                'tanggal_mulai' => now()->subDays(7),
                'tanggal_selesai' => now()->subDays(6),
                'status_pemesanan' => 'disetujui_final',
                'catatan' => 'Sample pemesanan seed',
            ]
        );

        LogPersetujuan::updateOrCreate(
            [
                'pemesanan_id' => $pemesanan->id,
                'penyetujui_id' => $approver1Id,
                'level' => 1,
            ],
            [
                'aksi' => 'setuju',
                'catatan_tambahan' => 'Approved L1',
            ]
        );

        LogPersetujuan::updateOrCreate(
            [
                'pemesanan_id' => $pemesanan->id,
                'penyetujui_id' => $approver2Id,
                'level' => 2,
            ],
            [
                'aksi' => 'setuju',
                'catatan_tambahan' => 'Approved L2',
            ]
        );

        RiwayatPemakaian::updateOrCreate(
            ['pemesanan_id' => $pemesanan->id],
            [
                'jarak_tempuh_km' => 120,
                'bbm_terpakai_liter' => 15,
                'keterangan' => 'Riwayat pemakaian seed',
            ]
        );
    }
}
