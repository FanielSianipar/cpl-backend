<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use Illuminate\Database\Seeder;

class FakultasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Fakultas::create([
            'kode_fakultas' => 'FEB',
            'nama_fakultas' => 'Fakultas Ekonomi dan Bisnis',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FISIP',
            'nama_fakultas' => 'Fakultas Ilmu Sosial dan Ilmu Politik',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FMIPA',
            'nama_fakultas' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FP',
            'nama_fakultas' => 'Fakultas Pertanian',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FT',
            'nama_fakultas' => 'Fakultas Teknik',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FK',
            'nama_fakultas' => 'Fakultas Kedokteran',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FKIP',
            'nama_fakultas' => 'Fakultas Keguruan dan Ilmu Pendidikan',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FH',
            'nama_fakultas' => 'Fakultas Hukum',
        ]);
    }
}
