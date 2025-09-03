<?php

namespace Database\Seeders;

use App\Models\Baju;
use App\Models\KategoriBaju;
use Illuminate\Database\Seeder;

class BajuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (KategoriBaju::count() === 0) {
            // Pastikan ada beberapa kategori untuk relasi
            KategoriBaju::factory()->count(3)->create();
        }

        Baju::factory()->count(20)->create();
    }
}

