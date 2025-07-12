<?php

namespace Database\Factories;

use App\Models\Penilaian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penilaian>
 */
class PenilaianFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Penilaian::class;

    /**
     * Daftar jenis penilaian yang tersedia.
     *
     * @var array
     */
    protected $jenisPenilaian = ['Tugas', 'Kuis', 'UTS', 'UAS', 'Projek', 'Keaktifan Partisipan'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_penilaian' => fake()->randomElement($this->jenisPenilaian),
        ];
    }
}
