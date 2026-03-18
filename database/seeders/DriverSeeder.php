<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Budi', 'telepon' => '081200000001', 'status' => 'tersedia'],
            ['nama' => 'Andi', 'telepon' => '081200000002', 'status' => 'tersedia'],
        ];

        foreach ($data as $row) {
            Driver::updateOrCreate(['telepon' => $row['telepon']], $row);
        }
    }
}
