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
        Schema::create('cpl_mata_kuliah', function (Blueprint $table) {
            $table->id('cpl_mata_kuliah_id');
            $table->unsignedBigInteger('cpl_id');
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->decimal('bobot', 5, 2)->comment('Contoh: 30.00 untuk 30%');
            $table->timestamps();

            // Mendefinisikan foreign key, pastikan nama tabel dan kolom sesuai dengan definisi Anda
            $table->foreign('mata_kuliah_id')
                ->references('mata_kuliah_id')->on('mata_kuliah')
                ->onDelete('cascade');

            $table->foreign('cpl_id')
                ->references('cpl_id')->on('cpl')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpl_mata_kuliah');
    }
};
