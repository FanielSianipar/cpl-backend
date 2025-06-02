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
        Schema::create('prodi', function (Blueprint $table) {
            $table->id('prodi_id');
            $table->string('kode_prodi', 10);
            $table->string('nama_prodi', 50);
            $table->unsignedBigInteger('fakultas_id');
            $table->foreign('fakultas_id')
                ->references('fakultas_id')
                ->on('fakultas')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodi');
    }
};
