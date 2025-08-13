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
        Schema::create('kelas_mahasiswa', function (Blueprint $table) {
            $table->id('kelas_mahasiswa_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->timestamps();

            // Constraints
            $table->foreign('kelas_id')->references('kelas_id')->on('kelas')->cascadeOnDelete();
            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa')->cascadeOnDelete();

            $table->unique(['kelas_id', 'mahasiswa_id'], 'kelas_mahasiswa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_mahasiswa');
    }
};
