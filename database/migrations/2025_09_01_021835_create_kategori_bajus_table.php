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
        Schema::create('kategori_bajus', function (Blueprint $table) {
            // Kolom id sebagai Primary Key (BIGINT, auto-increment)
            $table->id();

            // Kolom untuk nama kategori (VARCHAR 100 karakter, tidak boleh kosong)
            $table->string('nama_kategori', 100);

            // Kolom created_at dan updated_at (TIMESTAMP)
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
        Schema::dropIfExists('kategori_bajus');
    }
};
