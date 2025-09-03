<?php

namespace Database\Factories;

use App\Models\Transaksi;
use App\Models\User;
use App\Models\Baju;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaksi>
 */
class TransaksiFactory extends Factory
{
    protected $model = Transaksi::class;

    public function definition(): array
    {
        $jenis = $this->faker->randomElement(['masuk', 'keluar']);
        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'baju_id' => Baju::query()->inRandomOrder()->value('id') ?? Baju::factory(),
            'jenis_transaksi' => $jenis,
            'jumlah' => $this->faker->numberBetween(1, 20),
            'tanggal' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'keterangan' => $this->faker->optional()->randomElement(['restock', 'penjualan', 'retur', 'stok awal']),
        ];
    }
}

