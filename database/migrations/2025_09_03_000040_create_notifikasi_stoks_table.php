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
        Schema::create('notifikasi_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baju_id')->constrained('bajus');
            $table->enum('status', ['warning', 'critical']);
            $table->string('pesan', 255)->nullable();
            $table->boolean('terbaca')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi_stoks');
    }
};

