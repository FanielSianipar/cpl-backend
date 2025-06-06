<?php

namespace Database\Factories;

use App\Models\CPMK;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cpmk>
 */
class CpmkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CPMK::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Misalnya kode CPMK menggunakan pola 'k_cpmk####' yang unik
            'kode_cpmk' => $this->faker->unique()->numerify('k_cpmk####'),
            // Field nama CPMK
            'nama_cpmk' => $this->faker->words(3, true),
            // Deskripsi CPMK
            'deskripsi' => $this->faker->paragraph(),
            // Jika tidak diberikan, factory Prodi akan membuat instance Prodi baru
            'prodi_id' => function () {
                return Prodi::factory()->create()->prodi_id;
            },
        ];
    }
}
