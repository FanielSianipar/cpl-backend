<?php

namespace Database\Factories;

use App\Models\CPL;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cpl>
 */
class CplFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CPL::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Misalnya kode CPL menggunakan pola 'k_cpl####' yang unik
            'kode_cpl' => $this->faker->unique()->numerify('k_cpl####'),
            // Field nama CPL
            'nama_cpl' => $this->faker->words(3, true),
            // Deskripsi CPL
            'deskripsi' => $this->faker->paragraph(),
            // Jika tidak diberikan, factory Prodi akan membuat instance Prodi baru
            'prodi_id' => function () {
                return Prodi::factory()->create()->prodi_id;
            },
        ];
    }
}
