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
        Schema::create('sub_penilaian', function (Blueprint $table) {
            $table->bigIncrements('sub_penilaian_id');
            $table->unsignedBigInteger('penilaian_id');
            $table->unsignedBigInteger('kelas_id');
            $table->string('nama_sub_penilaian', 50); // e.g. "Tugas 1", "Tugas 2"
            $table->timestamps();

            $table->foreign('penilaian_id')
                ->references('penilaian_id')
                ->on('penilaian')
                ->onDelete('cascade');
            $table->foreign('kelas_id')
                ->references('kelas_id')
                ->on('kelas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_penilaian');
    }
};
