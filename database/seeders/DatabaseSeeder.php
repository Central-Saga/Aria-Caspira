<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KategoriBajuSeeder::class,
            BajuSeeder::class,
            TransaksiSeeder::class,
            NotifikasiStokSeeder::class,
        ]);
    }
}
