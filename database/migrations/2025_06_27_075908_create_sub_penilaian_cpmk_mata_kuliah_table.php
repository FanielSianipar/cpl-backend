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
            $table->id('sub_penilaian_cpmk_mata_kuliah_id');
            $table->unsignedBigInteger('sub_penilaian_id');
            $table->unsignedBigInteger('cpmk_id');
            $table->decimal('bobot', 5, 2)
                ->comment('≤ bobot pada cpmk_mata_kuliah pivot');
            $table->timestamps();

            // FKs
            $table->foreign('sub_penilaian_id')
                ->references('sub_penilaian_id')
                ->on('sub_penilaian')
                ->onDelete('cascade');
            $table->foreign('cpmk_id')
                ->references('cpmk_id')
                ->on('cpmk')
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
