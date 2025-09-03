<?php

namespace Database\Seeders;

use App\Models\Baju;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Baju::count() === 0) {
            // Pastikan ada data baju
            Baju::factory()->count(5)->create();
        }

        // Buat transaksi acak sekaligus sesuaikan stok
        Transaksi::factory()->count(25)->create()->each(function ($t) {
            /** @var Transaksi $t */
            $baju = $t->baju()->lockForUpdate()->first();
            if (!$baju) return;
            DB::transaction(function () use ($t, $baju) {
                $delta = $t->jenis_transaksi === 'masuk' ? $t->jumlah : -$t->jumlah;
                $baju->increment('stok_tersedia', $delta);
            });
        });
    }
}

