<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Permission;

/**
 * @extends Factory<\Spatie\Permission\Models\Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'guard_name' => 'web',
        ];
    }
}

