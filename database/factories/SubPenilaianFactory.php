<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\SubPenilaian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubPenilaian>
 */
class SubPenilaianFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubPenilaian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'penilaian_id' => function () {
                return Penilaian::factory()->create()->penilaian_id;
            },
            'kelas_id' => function () {
                return Kelas::factory()->create()->kelas_id;
            },
            // Field nama sub_penilaian
            'nama_sub_penilaian' => $this->faker->words(3, true),
        ];
    }
}
