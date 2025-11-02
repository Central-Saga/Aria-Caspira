<?php

namespace Database\Factories;

use App\Models\Baju;
use App\Models\KategoriBaju;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Baju>
 */
class BajuFactory extends Factory
{
    protected $model = Baju::class;

    public function definition(): array
    {
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $colors = ['Hitam', 'Putih', 'Biru', 'Merah', 'Hijau', 'Abu-abu'];

        return [
            // 80% ada kategori, jika tidak akan null
            'kategori_baju_id' => $this->faker->boolean(80)
                ? (KategoriBaju::query()->inRandomOrder()->value('id') ?? KategoriBaju::factory())
                : null,
            'nama_baju' => ucfirst($this->faker->words(2, true)),
            'ukuran' => $this->faker->optional()->randomElement($sizes),
            'warna' => $this->faker->optional()->randomElement($colors),
            'harga' => $this->faker->randomFloat(2, 20000, 500000),
            'stok_tersedia' => $this->faker->numberBetween(0, 150),
        ];
    }
}

