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
        Schema::create('cpmk', function (Blueprint $table) {
            $table->id('cpmk_id');
            $table->string('kode_cpmk', 10);
            $table->string('nama_cpmk', 50);
            $table->text('deskripsi');

            // jika cpmk hanya dipakai di satu mata kuliah
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->foreign('mata_kuliah_id')
                ->references('mata_kuliah_id')
                ->on('mata_kuliah')
                ->onDelete('cascade');

            // jika cpmk dipakai berulang, maka memakai many-to-many
            // $table->unsignedBigInteger('prodi_id');
            // $table->foreign('prodi_id')
            //     ->references('prodi_id')
            //     ->on('prodi')
            //     ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpmk');
    }
};
