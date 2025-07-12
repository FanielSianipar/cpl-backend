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
        Schema::create('sub_penilaian_cpmk_mata_kuliah', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_penilaian_id');
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->unsignedBigInteger('cpmk_id');
            $table->unsignedBigInteger('cpl_id');
            $table->decimal('bobot', 5, 2)
                ->comment('≤ bobot pada cpmk_mata_kuliah pivot');
            $table->timestamps();

            // Composite primary key
            $table->primary(['sub_penilaian_id', 'mata_kuliah_id', 'cpmk_id', 'cpl_id']);

            // FKs
            $table->foreign('sub_penilaian_id')
                ->references('sub_penilaian_id')
                ->on('sub_penilaian')
                ->onDelete('cascade');
            $table->foreign(['mata_kuliah_id', 'cpmk_id', 'cpl_id'], 'fk_subpenilaian_cpmk_mk')
                ->references(['mata_kuliah_id', 'cpmk_id', 'cpl_id'])
                ->on('cpmk_mata_kuliah')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_penilaian_cpmk_mata_kuliah');
    }
};
