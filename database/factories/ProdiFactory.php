<?php

namespace Database\Factories;

use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prodi>
 */
class ProdiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Prodi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Membuat kode prodi unik dengan format PRD###
            'kode_prodi' => $this->faker->unique()->bothify('PRD###'),
            // Menghasilkan nama prodi dengan 3 kata
            'nama_prodi' => $this->faker->words(3, true),
            // Mengaitkan dengan Fakultas
            'fakultas_id' => Fakultas::factory()
        ];
    }
}
