<?php

namespace Database\Seeders;

use App\Models\Distributor;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    public function run(): void
    {
        $distributors = [
            ['name' => 'PT Kimia Farma Trading & Distribution', 'phone' => '0341-555001', 'address' => 'Jl. Soekarno Hatta, Malang'],
            ['name' => 'PT Enseval Putra Megatrading', 'phone' => '0341-555002', 'address' => 'Jl. Raya Kediri, Kediri'],
            ['name' => 'PT Merapi Utama Pharma', 'phone' => '0341-555003', 'address' => 'Jl. Panglima Sudirman, Surabaya'],
            ['name' => 'PT Anugrah Argon Medica', 'phone' => '0341-555004', 'address' => 'Jl. A. Yani, Surabaya'],
        ];

        foreach ($distributors as $dist) {
            Distributor::create($dist);
        }
    }
}
