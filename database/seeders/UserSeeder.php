<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed users for development/login.
     */
    public function run(): void
    {
        // 3 akun utama
        $super = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'role' => 'Super Admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);

        $staff = User::factory()->create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'role' => 'Staff',
        ]);

        // Tambahan contoh pengguna
        User::factory(5)->create();
    }
}
