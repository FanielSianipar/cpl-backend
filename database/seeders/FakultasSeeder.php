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
            'kode_fakultas' => 'FK01',
            'nama_fakultas' => 'Fakultas Teknik',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FK02',
            'nama_fakultas' => 'Fakultas Ekonomi',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FK03',
            'nama_fakultas' => 'Fakultas Hukum',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FK04',
            'nama_fakultas' => 'Fakultas Sastra',
        ]);

        Fakultas::create([
            'kode_fakultas' => 'FK05',
            'nama_fakultas' => 'Fakultas Ilmu Sosial',
        ]);
    }
}
