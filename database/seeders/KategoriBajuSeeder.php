<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriBaju; 

class KategoriBajuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_kategori' => 'Kemeja Formal'],
            ['nama_kategori' => 'Kaos Polos'],
            ['nama_kategori' => 'Jaket Hoodie'],
            ['nama_kategori' => 'Celana Jeans'],
            ['nama_kategori' => 'Gaun Pesta'],
            ['nama_kategori' => 'Batik Modern'],
            ['nama_kategori' => 'Pakaian Olahraga'],
            ['nama_kategori' => 'Sweater'],
            ['nama_kategori' => 'Rok Mini'],
            ['nama_kategori' => 'Blazer'],
        ];

        foreach ($data as $item) {
