<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MataKuliah>
 */
class MataKuliahFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MataKuliah::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Misalnya kode mata kuliah menggunakan pola 'kode_mk####' yang unik
            'kode_mata_kuliah' => $this->faker->unique()->bothify('kode_mk####'),
            // Field nama mata kuliah
            'nama_mata_kuliah' => $this->faker->words(3, true),
            // Jika tidak diberikan, factory Prodi akan membuat instance Prodi baru
            'prodi_id' => function () {
                return Prodi::factory()->create()->prodi_id;
            },
        ];
    }
}
