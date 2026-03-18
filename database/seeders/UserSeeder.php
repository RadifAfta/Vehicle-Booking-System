<?php

namespace Database\Seeders;

use App\Models\Kantor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $kantorPusatId = Kantor::where('tipe', 'kantor_pusat')->value('id');

        $users = [
            [
                'nama' => 'Admin1',
                'email' => 'admin@booking.test',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'kantor_id' => $kantorPusatId,
            ],
            [
                'nama' => 'Adi Saputra',
                'email' => 'approver1@booking.test',
                'password' => Hash::make('password123'),
                'role' => 'penyetujui',
                'kantor_id' => $kantorPusatId,
            ],
            [
                'nama' => 'Budi Santoso',
                'email' => 'approver2@booking.test',
                'password' => Hash::make('password123'),
                'role' => 'penyetujui',
                'kantor_id' => $kantorPusatId,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'nama' => $user['nama'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'kantor_id' => $user['kantor_id'],
                ]
            );
        }
    }
}
