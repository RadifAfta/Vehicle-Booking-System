<?php

namespace Database\Seeders;

use App\Models\Kantor;
use Illuminate\Database\Seeder;

class KantorSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Kantor Pusat Jakarta', 'tipe' => 'kantor_pusat', 'wilayah' => 'DKI Jakarta'],
            ['nama' => 'Kantor Cabang Morowali', 'tipe' => 'kantor_cabang', 'wilayah' => 'Sulawesi Tengah'],
            ['nama' => 'Tambang Konawe', 'tipe' => 'tambang', 'wilayah' => 'Sulawesi Tenggara'],
            ['nama' => 'Tambang Halmahera', 'tipe' => 'tambang', 'wilayah' => 'Maluku Utara'],
            ['nama' => 'Tambang Kolaka', 'tipe' => 'tambang', 'wilayah' => 'Sulawesi Tenggara'],
            ['nama' => 'Tambang Pomalaa', 'tipe' => 'tambang', 'wilayah' => 'Sulawesi Tenggara'],
            ['nama' => 'Tambang Obi', 'tipe' => 'tambang', 'wilayah' => 'Maluku Utara'],
            ['nama' => 'Tambang Bahodopi', 'tipe' => 'tambang', 'wilayah' => 'Sulawesi Tengah'],
        ];

        foreach ($data as $row) {
            Kantor::updateOrCreate(['nama' => $row['nama']], $row);
        }
    }
}
