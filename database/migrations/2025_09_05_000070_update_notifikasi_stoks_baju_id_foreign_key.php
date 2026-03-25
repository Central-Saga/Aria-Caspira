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
        Schema::table('notifikasi_stoks', function (Blueprint $table) {
            $table->dropForeign(['baju_id']);
            $table->foreign('baju_id')->references('id')->on('bajus')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasi_stoks', function (Blueprint $table) {
            $table->dropForeign(['baju_id']);
            $table->foreign('baju_id')->references('id')->on('bajus');
        });
    }
};
