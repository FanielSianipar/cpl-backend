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
        Schema::create('cpmk_mata_kuliah', function (Blueprint $table) {
            $table->id('cpmk_mata_kuliah_id');
            // $table->unsignedBigInteger('mata_kuliah_id');
            $table->unsignedBigInteger('cpmk_id');
            $table->unsignedBigInteger('cpl_id');
            $table->decimal('bobot', 5, 2)->comment('Bobot CPMK; total untuk masing-masing CPL harus ≤ bobot CPL');
            $table->timestamps();

            // jika cpmk dipakai berulang, maka memakai many-to-many
            // $table->primary(['mata_kuliah_id', 'cpmk_id', 'cpl_id']);
            // $table->foreign('mata_kuliah_id')
            //     ->references('mata_kuliah_id')->on('mata_kuliah')
            //     ->onDelete('cascade');

            $table->foreign('cpmk_id')
                ->references('cpmk_id')->on('cpmk')
                ->onDelete('cascade');

            $table->foreign('cpl_id')
                ->references('cpl_id')->on('cpl')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpmk_mata_kuliah');
    }
};
