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
        // Perintah ini akan memanggil semua seeder yang kita butuhkan
        $this->call([
            UserSeeder::class, // Ini akan membuat admin@example.com
            KategoriBajuSeeder::class, // Ini akan mengisi data kategori baju
        ]);
    }
}