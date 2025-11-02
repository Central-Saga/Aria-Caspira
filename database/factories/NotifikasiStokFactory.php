<?php

namespace Database\Factories;

use App\Models\NotifikasiStok;
use App\Models\Baju;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotifikasiStok>
 */
class NotifikasiStokFactory extends Factory
{
    protected $model = NotifikasiStok::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['warning', 'critical']);
        return [
            'baju_id' => Baju::query()->inRandomOrder()->value('id') ?? Baju::factory(),
            'status' => $status,
            'pesan' => $this->faker->optional()->sentence(6),
            'terbaca' => $this->faker->boolean(30),
        ];
    }
}

