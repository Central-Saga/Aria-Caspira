<?php

namespace Database\Factories;

use App\Models\KategoriBaju;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KategoriBaju>
 */
class KategoriBajuFactory extends Factory
{
    protected $model = KategoriBaju::class;

    public function definition(): array
    {
        $preset = [
            'Oversize',
            'Croptop',
            'Boxy',
            'Formal',
            'Casual',
            'Basic',
        ];

        return [
            'nama_kategori' => $this->faker->unique()->randomElement($preset),
        ];
    }
}

