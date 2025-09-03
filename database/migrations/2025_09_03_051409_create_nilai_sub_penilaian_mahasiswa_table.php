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
        Schema::create('nilai_sub_penilaian_mahasiswa', function (Blueprint $table) {
            $table->id('nilai_sub_penilaian_mahasiswa_id');
            $table->unsignedBigInteger('sub_penilaian_cpmk_mata_kuliah_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->decimal('nilai_mentah',  5, 2);
            $table->decimal('nilai_terbobot', 5, 2);
            $table->timestamps();

            // beri nama foreign key sendiri, misal "fk_nspm_spcpmk"
            $table->foreign(
                'sub_penilaian_cpmk_mata_kuliah_id',
                'fk_nspm_spcpmk'
            )
                ->references('sub_penilaian_cpmk_mata_kuliah_id')
                ->on('sub_penilaian_cpmk_mata_kuliah')
                ->cascadeOnDelete();

            // dan untuk mahasiswa
            $table->foreign(
                'mahasiswa_id',
                'fk_nspm_mhs'
            )
                ->references('mahasiswa_id')
                ->on('mahasiswa')
                ->cascadeOnDelete();

            // unique index, juga beri nama singkat
            $table->unique(
                ['sub_penilaian_cpmk_mata_kuliah_id', 'mahasiswa_id'],
                'uq_nspm_pivot_mahasiswa'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_sub_penilaian_mahasiswa');
    }
};
