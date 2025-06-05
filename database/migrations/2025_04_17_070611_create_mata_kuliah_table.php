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
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id('mata_kuliah_id');
            $table->string('kode_mata_kuliah', 10);
            $table->string('nama_mata_kuliah', 50);
            $table->unsignedBigInteger('prodi_id');
            $table->foreign('prodi_id')
                ->references('prodi_id') // Referensi ke kolom 'id' di tabel fakultas
                ->on('prodi')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
