<?php

namespace Database\Seeders;

use App\Models\Kantor;
use App\Models\Kendaraan;
use Illuminate\Database\Seeder;

class KendaraanSeeder extends Seeder
{
    public function run(): void
    {
        $kantorPusatId = Kantor::where('tipe', 'kantor_pusat')->value('id');

        $data = [
            [
                'nama' => 'Toyota Hiace 01',
                'jenis' => 'angkutan_orang',
                'kepemilikan' => 'milik_perusahaan',
                'kantor_id' => $kantorPusatId,
                'konsumsi_bbm_liter_per_km' => 0.12,
                'tanggal_servis_terakhir' => now()->subDays(20)->toDateString(),
            ],
            [
                'nama' => 'Mitsubishi L300 01',
                'jenis' => 'angkutan_barang',
                'kepemilikan' => 'sewa',
                'kantor_id' => 3,
                'konsumsi_bbm_liter_per_km' => 0.18,
                'tanggal_servis_terakhir' => now()->subDays(35)->toDateString(),
            ],
            [
                'nama' => 'Daihatsu Gran Max 01',
                'jenis' => 'angkutan_barang',
                'kepemilikan' => 'sewa',
                'kantor_id' => 4,
                'konsumsi_bbm_liter_per_km' => 0.18,
                'tanggal_servis_terakhir' => now()->subDays(35)->toDateString(),
            ],
            [
                'nama' => 'Fuso Canter 01',
                'jenis' => 'angkutan_barang',
                'kepemilikan' => 'sewa',
                'kantor_id' => 5,
                'konsumsi_bbm_liter_per_km' => 0.18,
                'tanggal_servis_terakhir' => now()->subDays(35)->toDateString(),
            ],
        ];

        foreach ($data as $row) {
            Kendaraan::updateOrCreate(['nama' => $row['nama']], $row);
        }
    }
}
