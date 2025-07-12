<?php

namespace Database\Seeders;

use App\Models\Penilaian;
use Illuminate\Database\Seeder;

class PenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $penilaians = ['Tugas', 'Kuis', 'UTS', 'UAS', 'Projek', 'Keaktifan Partisipan'];

        foreach ($penilaians as $penilaian) {
            Penilaian::create(['nama_penilaian' => $penilaian]);
        }
    }
}
