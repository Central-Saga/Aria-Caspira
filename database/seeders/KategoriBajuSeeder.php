<?php

namespace Database\Seeders;

use App\Models\KategoriBaju;
use Illuminate\Database\Seeder;

class KategoriBajuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Oversize',
            'Croptop',
            'Boxy',
        ];

        foreach ($data as $nama) {
            KategoriBaju::firstOrCreate(['nama_kategori' => $nama]);
        }
    }
}

