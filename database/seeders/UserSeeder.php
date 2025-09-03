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
        // Primary login user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            // password is set by factory to 'password'
        ]);

        // Optionally a demo user
        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
        ]);

        // Additional random users
        User::factory(3)->create();
    }
}

