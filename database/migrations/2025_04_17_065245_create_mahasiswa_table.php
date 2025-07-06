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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id('mahasiswa_id');
            $table->string('npm', 15)->unique();
            $table->string('name', 50);
            $table->year('angkatan');
            $table->string('email', 50)->unique();
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
        Schema::dropIfExists('mahasiswa');
    }
};
