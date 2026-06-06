<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin Klinik',
            'email'    => 'admin@klinikdokterku.id',
            'password' => Hash::make('klinik2024'),
        ]);
    }
}
