<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\NilaiSubPenilaianMahasiswa;
use App\Models\Prodi;
use App\Models\SubPenilaianCpmkMataKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KaprodiService
{
    /**
     * Ambil daftar mata kuliah beserta kelas
     */
    public function melihatDaftarMataKuliah(): array
    {
        $kelasList = Kelas::with('mataKuliah')
            ->whereHas('mataKuliah')
            ->get(['kelas_id', 'mata_kuliah_id', 'nama_kelas', 'tahun_ajaran', 'semester']);

        $data = $kelasList->map(function ($kelas) {
            $semester = $kelas->semester;

            if (is_numeric($semester)) {
                $ganjilGenap = ((int) $semester % 2 === 0) ? 'Genap' : 'Ganjil';
            } else {
                $ganjilGenap = ucfirst($semester);
            }

            return [
                'kode_mk'          => $kelas->mataKuliah->kode_mata_kuliah,
                'nama_mata_kuliah' => $kelas->mataKuliah->nama_mata_kuliah,
                'nama_kelas'       => $kelas->nama_kelas,
                'periode'          => "{$kelas->tahun_ajaran} {$ganjilGenap}",
                'kelas_id'         => $kelas->kelas_id,
            ];
        })->toArray();

        return [
            'data'    => $data,
            'message' => 'Daftar mata kuliah berhasil diambil.',
        ];
    }

    /**
     * Tangani aksi view, store, update, delete nilai sub-penilaian mahasiswa.
     *
     * @param  array  $data  [
     *     'action'                              => 'view'|'store'|'update'|'delete',
     *     'dosen_id'                            => int,
     *     'kelas_id'                            => int,
     *     'sub_penilaian_cpmk_mata_kuliah_id'   => int,
     *     // untuk store/update/delete
     *     'mahasiswa_id'                        => int,
     *     'nilai_mentah'                        => float,
     * ]
     * @return array ['data' => mixed, 'message' => string]
     */
    public function melihatHasilPerhitungan(array $data): array
    {
        if (!isset($data['action'])) {
            return ['message' => 'Action tidak ditemukan.'];
        }

        switch ($data['action']) {
            case 'view':
                return $this->viewNilai($data);
            case 'store':
            case 'update':
                return $this->storeOrUpdateNilai($data);
            case 'delete':
                return $this->deleteNilai($data);
            default:
                return ['message' => 'Action tidak valid.'];
        }
    }

    protected function ensureDosenAmpuKelas(array $data): void
    {
        $found = DB::table('kelas_dosen')
            ->where('kelas_id', $data['kelas_id'])
            ->where('dosen_id', $data['dosen_id'])
            ->exists();

        if (! $found) {
            throw new NotFoundHttpException('Anda tidak mengampu kelas ini.');
        }
    }

    protected function viewNilai(array $data): array
    {
        $this->ensureDosenAmpuKelas($data);

        $query = NilaiSubPenilaianMahasiswa::with('mahasiswa')
            ->where('sub_penilaian_cpmk_mata_kuliah_id', $data['sub_penilaian_cpmk_mata_kuliah_id']);

        if (isset($data['mahasiswa_id'])) {
            $query->where('mahasiswa_id', $data['mahasiswa_id']);
        }

        return [
            'data'    => $query->get(),
            'message' => 'Nilai berhasil diambil.',
        ];
    }

    protected function storeOrUpdateNilai(array $data): array
    {
        foreach (['kelas_id', 'sub_penilaian_cpmk_mata_kuliah_id', 'mahasiswa_id', 'nilai_mentah'] as $key) {
            if (!isset($data[$key])) {
                throw ValidationException::withMessages([
                    $key => ["Field {$key} wajib diisi untuk aksi {$data['action']}."]
                ]);
            }
        }

        $this->ensureDosenAmpuKelas($data);

        $pivot = SubPenilaianCpmkMataKuliah::with('subPenilaian')
            ->findOrFail($data['sub_penilaian_cpmk_mata_kuliah_id']);

        if ($pivot->subPenilaian->kelas_id !== $data['kelas_id']) {
            throw new NotFoundHttpException('Mapping sub-penilaian ↔ CPMK tidak ditemukan di kelas ini.');
        }

        $raw      = (float) $data['nilai_mentah'];
        $weighted = round($raw * ($pivot->bobot / 100), 2);

        $record = NilaiSubPenilaianMahasiswa::updateOrCreate(
            [
                'sub_penilaian_cpmk_mata_kuliah_id' => $pivot->sub_penilaian_cpmk_mata_kuliah_id,
                'mahasiswa_id'                     => $data['mahasiswa_id'],
            ],
            [
                'nilai_mentah'   => $raw,
                'nilai_terbobot' => $weighted,
            ]
        );

        return [
            'data'    => $record,
            'message' => 'Nilai berhasil ' . ($data['action'] === 'store' ? 'ditambahkan.' : 'diperbarui.'),
        ];
    }

    protected function deleteNilai(array $data): array
    {
        if (!isset($data['kelas_id'], $data['sub_penilaian_cpmk_mata_kuliah_id'], $data['mahasiswa_id'])) {
            return ['message' => 'kelas_id, sub_penilaian_cpmk_mata_kuliah_id, dan mahasiswa_id diperlukan untuk delete.'];
        }

        $this->ensureDosenAmpuKelas($data);

        $deleted = NilaiSubPenilaianMahasiswa::where([
            'sub_penilaian_cpmk_mata_kuliah_id' => $data['sub_penilaian_cpmk_mata_kuliah_id'],
            'mahasiswa_id'                     => $data['mahasiswa_id'],
        ])->delete();

        return $deleted
            ? ['message' => 'Nilai berhasil dihapus.']
            : ['message' => 'Nilai tidak ditemukan atau sudah dihapus.'];
    }
}
