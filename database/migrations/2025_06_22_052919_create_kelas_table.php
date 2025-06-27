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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id('kelas_id');
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->string('kode_kelas', 15);
            $table->string('nama_kelas', 50);
            $table->unsignedBigInteger('dosen_id')->nullable(); // Opsional
            $table->string('semester', 20)->nullable();
            $table->string('tahun_ajaran', 10)->nullable();
            $table->timestamps();

            $table->foreign('mata_kuliah_id')
                ->references('mata_kuliah_id')
                ->on('mata_kuliah')
                ->onDelete('cascade');

            // Jika dosen disimpan di tabel 'users', tambahkan FK seperti berikut:
            // $table->foreign('dosen_id')
            //       ->references('id')
            //       ->on('users')
            //       ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kelas');
    }
};
