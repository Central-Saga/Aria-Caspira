<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('bajus', function (Blueprint $table) {
            // id (PK), BIGINT, Primary key
            $table->id();

            // kategori_baju_id (FK), BIGINT, Relasi ke kategori_bajus.id
            $table->foreignId('kategori_baju_id')
                  ->nullable() // Boleh null jika belum ada kategori
                  ->constrained('kategori_bajus') // Merujuk ke tabel 'kategori_bajus'
                  ->onDelete('set null'); // Jika kategori dihapus, kolom ini menjadi NULL

            // nama_baju, VARCHAR(150)
            $table->string('nama_baju', 150);

            // ukuran, VARCHAR(20), boleh null
            $table->string('ukuran', 20)->nullable();

            // warna, VARCHAR(50), boleh null
            $table->string('warna', 50)->nullable();

            // harga, DECIMAL(12,2)
            $table->decimal('harga', 12, 2);

            // stok_tersedia, INT, default 0
            $table->integer('stok_tersedia')->default(0);

            // created_at dan updated_at, TIMESTAMP
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('bajus');
    }
};