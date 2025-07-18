<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('kelas_dosen', function (Blueprint $table) {
            // composite PK: satu dosen hanya satu role per kelas
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('dosen_id');
            $table->enum('jabatan', ['Dosen Utama', 'Pendamping Dosen 1', 'Pendamping Dosen 2']);
            $table->timestamps();

            $table->primary(['kelas_id', 'dosen_id']);

            $table->foreign('kelas_id')
                ->references('kelas_id')
                ->on('kelas')
                ->onDelete('cascade');

            $table->foreign('dosen_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_dosen');
    }
};
