<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mengambil semua fakultas dari database
        $fakultases = Fakultas::all();

        Prodi::create([
            'kode_prodi' => 'FT-PR01',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => $fakultases->firstWhere('kode_fakultas', 'FT')->fakultas_id,
        ]);

        // Untuk setiap fakultas, buat 3 prodi
        foreach ($fakultases as $fakultas) {
            for ($i = 1; $i <= 3; $i++) {
                Prodi::create([
                    // Menghasilkan kode prodi dengan menggabungkan kode fakultas dengan nomor prodi
                    'kode_prodi' => $fakultas->kode_fakultas . '-PR' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    // Misalnya: Fakultas Teknik Prodi 1, Fakultas Teknik Prodi 2, dst.
                    'nama_prodi' => $fakultas->kode_fakultas . ' Prodi ' . $i,
                    'fakultas_id' => $fakultas->fakultas_id,
                ]);
            }
        }
    }
}
