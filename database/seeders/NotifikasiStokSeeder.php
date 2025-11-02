<?php

namespace Database\Seeders;

use App\Models\Baju;
use App\Models\NotifikasiStok;
use Illuminate\Database\Seeder;

class NotifikasiStokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Baju::count() === 0) {
            Baju::factory()->count(5)->create();
        }

        NotifikasiStok::factory()->count(12)->create();
    }
}

