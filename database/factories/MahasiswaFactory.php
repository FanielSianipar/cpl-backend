<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mahasiswa::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Misalnya npm menggunakan pola '2015061####' yang unik
            'npm' => $this->faker->unique()->numerify('2015061####'),
            // Field name mahasiswa
            'name' => $this->faker->name(),
            // Email yang unik untuk setiap mahasiswa
            'email' => $this->faker->unique()->safeEmail(),
            // Jika tidak diberikan, factory Prodi akan membuat instance Prodi baru
            'prodi_id' => function () {
                return Prodi::factory()->create()->prodi_id;
            },
        ];
    }
}
