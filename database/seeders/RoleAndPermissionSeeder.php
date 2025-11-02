<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Delegasikan ke seeder utama agar nama sesuai referensi juga tersedia
        $this->call(RolesAndPermissionsSeeder::class);
    }
}

