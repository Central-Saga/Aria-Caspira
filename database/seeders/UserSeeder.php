<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // <-- 1. Import model User
use Illuminate\Support\Facades\Hash; // <-- 2. Import Hash untuk enkripsi password

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 3. Perintah untuk membuat user baru
        User::create([
            'name' => 'Admin', // Nama user
            'email' => 'admin@example.com', // Email untuk login
            'password' => Hash::make('password'), // Passwordnya adalah 'password'
        ]);
    }
}