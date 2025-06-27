<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Kelas::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Misalnya kode kelas menggunakan pola 'kode_k####' yang unik
            'kode_kelas' => $this->faker->unique()->bothify('kode_k####'),
            // Field nama kelas
            'nama_kelas' => $this->faker->words(3, true),
            'semester' => $this->faker->numberBetween(1, 14),
            'tahun_ajaran' => $this->faker->year() . '/' . ($this->faker->year() + 1),
            // Jika tidak diberikan, factory MataKuliah akan membuat instance MataKuliah baru
            'mata_kuliah_id' => function () {
                return MataKuliah::factory()->create()->mata_kuliah_id;
            },
        ];
    }
}
