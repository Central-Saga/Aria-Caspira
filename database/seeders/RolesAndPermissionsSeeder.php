<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache agar update permission/role langsung berlaku
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Definisi permission sesuai modul aplikasi inventori
        $permissions = [
            'manage users',
            'manage roles',
            'manage categories',
            'manage baju',
            'manage transaksi',
            'manage notifications',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Buat roles
        $super = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        // Super Admin mendapatkan semua permission
        $super->syncPermissions(Permission::all());

        // Admin: kategori, baju, transaksi, notifikasi
        $admin->syncPermissions([
            'manage categories',
            'manage baju',
            'manage transaksi',
            'manage notifications',
        ]);

        // Staff: transaksi dan notifikasi
        $staff->syncPermissions([
            'manage transaksi',
            'manage notifications',
        ]);

        // Sinkronkan role ke user berdasarkan kolom `role`
        User::query()->each(function (User $user) use ($super, $admin, $staff) {
            $roleName = $user->role ?? 'Staff';
            $role = match ($roleName) {
                'Super Admin' => $super,
                'Admin' => $admin,
                default => $staff,
            };
            $user->syncRoles([$role]);
        });
    }
}
