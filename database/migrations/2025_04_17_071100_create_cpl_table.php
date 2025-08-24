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
        Schema::create('cpl', function (Blueprint $table) {
            $table->id('cpl_id');
            $table->string('kode_cpl', 10);
            $table->string('nama_cpl', 50);
            $table->text('deskripsi');
            $table->unsignedBigInteger('prodi_id');
            $table->foreign('prodi_id')
                ->references('prodi_id')
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
        Schema::dropIfExists('cpl');
    }
};
