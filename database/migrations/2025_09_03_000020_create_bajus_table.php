<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('bajus')) {
            return;
        }

        Schema::create('bajus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_baju_id')
                ->nullable()
                ->constrained('kategori_bajus')
                ->nullOnDelete();
            $table->string('nama_baju', 150);
            $table->string('ukuran', 20)->nullable();
            $table->string('warna', 50)->nullable();
            $table->decimal('harga', 12, 2);
            $table->integer('stok_tersedia')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('bajus')) {
            return;
        }

        Schema::dropIfExists('bajus');
    }
};
