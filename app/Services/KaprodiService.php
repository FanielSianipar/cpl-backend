<?php

namespace App\Services;

use App\Models\CPMK;
use App\Models\Kelas;
use App\Models\NilaiSubPenilaianMahasiswa;
use App\Models\SubPenilaianCpmkMataKuliah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KaprodiService
{
    public function nilaiCplSeluruhMataKuliah(?int $angkatan = null, ?string $tahunAjaran = null): array
    {
        // Hitung nilai per kelas per mahasiswa per CPL
        $nilaiPerKelas = DB::table('cpl')
            ->join('cpmk_mata_kuliah as cpmk_mk', 'cpl.cpl_id', '=', 'cpmk_mk.cpl_id')
            ->join('cpmk', 'cpmk_mk.cpmk_id', '=', 'cpmk.cpmk_id')
            ->join('sub_penilaian_cpmk_mata_kuliah as spcmk', 'cpmk.cpmk_id', '=', 'spcmk.cpmk_id')
            ->join('sub_penilaian as sp', 'spcmk.sub_penilaian_id', '=', 'sp.sub_penilaian_id')
            ->join('kelas as kls', 'sp.kelas_id', '=', 'kls.kelas_id')
            ->leftJoin('nilai_sub_penilaian_mahasiswa as nspm', 'spcmk.sub_penilaian_cpmk_mata_kuliah_id', '=', 'nspm.sub_penilaian_cpmk_mata_kuliah_id')
            ->leftJoin('mahasiswa as mhs', 'nspm.mahasiswa_id', '=', 'mhs.mahasiswa_id')
            // Filter angkatan jika diberikan
            ->when($angkatan, fn($q) => $q->where('mhs.angkatan', $angkatan))
            // Filter tahun ajaran jika diberikan
            ->when($tahunAjaran, fn($q) => $q->where('kls.tahun_ajaran', $tahunAjaran))
            ->groupBy('cpl.cpl_id', 'cpl.kode_cpl', 'kls.kelas_id', 'nspm.mahasiswa_id')
            ->select(
                'cpl.cpl_id',
                'cpl.kode_cpl',
                'nspm.mahasiswa_id',
                DB::raw('SUM(COALESCE(nspm.nilai_terbobot, 0)) / COUNT(DISTINCT spcmk.sub_penilaian_cpmk_mata_kuliah_id) as nilai_per_kelas')
            );

        // Hitung nilai CPL per mahasiswa (rata-rata nilai per kelas yang diambil)
        $nilaiPerMahasiswa = DB::query()
            ->fromSub($nilaiPerKelas, 'pk')
            ->groupBy('pk.cpl_id', 'pk.kode_cpl', 'pk.mahasiswa_id')
            ->select(
                'pk.cpl_id',
                'pk.kode_cpl',
                'pk.mahasiswa_id',
                DB::raw('AVG(pk.nilai_per_kelas) as nilai_cpl_per_mhs')
            );

        // Hitung rata-rata CPL antar mahasiswa
        $rows = DB::query()
            ->fromSub($nilaiPerMahasiswa, 'pm')
            ->groupBy('pm.cpl_id', 'pm.kode_cpl')
            ->select(
                'pm.kode_cpl',
                DB::raw('75 as target'), // Hardcode KKM
                DB::raw('AVG(pm.nilai_cpl_per_mhs) as aktual')
            )
            ->orderBy('pm.kode_cpl')
            ->get();

        // 4️⃣ Format siap Chart.js
        return [
            'labels'   => $rows->pluck('kode_cpl')->toArray(),
            'datasets' => [
                [
                    'label'           => 'Target CPL',
                    'data'            => $rows->pluck('target')->map(fn($v) => round($v, 2))->toArray(),
                    // 'borderColor'     => 'rgba(54, 162, 235, 1)',
                    // 'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    // 'borderDash'      => [5, 5],
                ],
                [
                    'label'           => 'Aktual CPL',
                    'data'            => $rows->pluck('aktual')->map(fn($v) => round($v, 2))->toArray(),
                    // 'borderColor'     => 'rgba(255, 99, 132, 1)',
                    // 'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                ],
            ],
        ];
    }

    /**
     * Ambil status upload nilai untuk semua kelas (per baris),
     * menampilkan hanya dosen dengan jabatan 'Dosen Utama'.
     *
     * @param  string|null  $tahunAjaran
     * @return array[]
     */
    public function statusPengisianNilai(string $tahunAjaran = null): array
    {
        $query = Kelas::select(['kelas_id', 'mata_kuliah_id', 'nama_kelas', 'semester', 'tahun_ajaran'])
            ->with(['mataKuliah:mata_kuliah_id,nama_mata_kuliah', 'dosens:id,name', 'subPenilaian.cpmks'])
            // load count mahasiswa terdaftar via pivot kelas_mahasiswa
            ->withCount('mahasiswas');

        if ($tahunAjaran) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }

        return $query->get()->map(function (Kelas $kelas) {
            $dosenUtama = $kelas->dosens
                ->filter(fn($dosen) => $dosen->pivot->jabatan === 'Dosen Utama')
                ->pluck('name')
                ->implode(' • ');

            $genapGanjil = $kelas->semester % 2 === 0 ? 'Genap' : 'Ganjil';
            $periode      = "{$kelas->tahun_ajaran} {$genapGanjil}";

            $pivotIds = $kelas->subPenilaian
                ->flatMap(
                    fn($subPenilaian) => $subPenilaian->cpmks
                        ->pluck('pivot.sub_penilaian_cpmk_mata_kuliah_id')
                )
                ->unique()
                ->toArray();

            $jumlahNilaiDiisi = empty($pivotIds) ? 0
                : NilaiSubPenilaianMahasiswa::whereIn(
                    'sub_penilaian_cpmk_mata_kuliah_id',
                    $pivotIds
                )
                ->distinct('mahasiswa_id')
                ->count('mahasiswa_id');

            $jumlahMahasiswaTerdaftar = $kelas->mahasiswas_count;

            $status = ($jumlahMahasiswaTerdaftar > 0 && $jumlahNilaiDiisi === $jumlahMahasiswaTerdaftar)
                ? 'Selesai'
                : 'Belum Selesai';

            return [
                'mata_kuliah' => $kelas->mataKuliah->nama_mata_kuliah,
                'nama_kelas'  => $kelas->nama_kelas,
                'dosen'       => $dosenUtama ?: '-',
                'periode'     => $periode,
                'status'      => $status,
            ];
        })->toArray();
    }

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
     * @param  int  $kelasId
     * @return array
     */
    public function detailPerhitunganPerkelas(int $kelasId): array
    {
        // Ambil kelas dengan relasi yang diperlukan
        $kelas = Kelas::with([
            'mataKuliah.prodi',
            'dosens',                    // semua dosen via pivot kelas_dosen
            'mahasiswas',                // semua mahasiswa
            'subPenilaian.cpmks.cpls',    // Sub‐penilaian → CPMK → CPL pivot
            'subPenilaian.penilaian',    // sub-penilaian → Penilaian
        ])->findOrFail($kelasId);

        $header = [
            'program_studi'    => $kelas->mataKuliah->prodi->nama_prodi,
            'tahun_ajaran'     => $kelas->tahun_ajaran,
            'kode_mata_kuliah' => $kelas->mataKuliah->kode_mata_kuliah,
            'nama_mata_kuliah' => $kelas->mataKuliah->nama_mata_kuliah,
            'nama_kelas'       => $kelas->nama_kelas,
            'dosen_pengampu'    => $kelas->dosens
                ->map(fn($dosen) => "{$dosen->name} ({$dosen->pivot->jabatan})")
                ->implode(' • '),
        ];

        // Daftar nama penilaian
        $daftarPenilaian = $kelas->subPenilaian
            ->pluck('penilaian.nama_penilaian')
            ->unique()
            ->values()
            ->toArray();

        // Kumpulkan hanya CPMK dari sub-penilaian di kelas ini
        $subPenilaianCpmks = $kelas->subPenilaian
            ->flatMap(fn($subPenilaian) => $subPenilaian->cpmks)
            ->unique('cpmk_id');

        // loop semua CPMK, setiap CPMK bisa punya banyak CPL via pivot
        $flat = $subPenilaianCpmks->flatMap(function (CPMK $cpmk) {
            // $cpmk adalah instance CPMK, kita loop semua CPL yang terkait via pivot
            return $cpmk->cpls->map(function ($cpl) use ($cpmk) {
                return [
                    'cpl_id'         => $cpl->cpl_id,
                    'kode_cpl'       => $cpl->kode_cpl,
                    'deskripsi_cpl'  => $cpl->deskripsi,
                    'cpmk_id'        => $cpmk->cpmk_id,
                    'kode_cpmk'      => $cpmk->kode_cpmk,
                    'deskripsi_cpmk' => $cpmk->deskripsi,
                ];
            });
        });

        // Pemetaan CPL ↔ CPMK dari cpmk_mata_kuliah
        $pemetaanCplCpmk = $flat
            ->groupBy('cpl_id')
            ->map(fn(Collection $rows, $cplId) => [
                'cpl_id'        => (int)$cplId,
                'kode_cpl'      => $rows->first()['kode_cpl'],
                'deskripsi_cpl' => $rows->first()['deskripsi_cpl'],
                'cpmks'         => $rows->map(fn($row) => [
                    'cpmk_id'        => $row['cpmk_id'],
                    'kode_cpmk'      => $row['kode_cpmk'],
                    'deskripsi_cpmk' => $row['deskripsi_cpmk'],
                ])->values()->toArray(),
            ])
            ->values()
            ->toArray();

        // Pemetaan sub-penilaian per penilaian (lengkap dgn CPMK & bobot)
        $pemetaanDanNilai = $kelas->subPenilaian
            ->flatMap(function ($subPenilaian) {
                // kembalikan Koleksi dari tiap CPMK di subPenilaian
                return $subPenilaian->cpmks->map(function ($cpmk) use ($subPenilaian) {
                    // hitung rata-rata nilai terbobot
                    $rerata = DB::table('nilai_sub_penilaian_mahasiswa')
                        ->where(
                            'sub_penilaian_cpmk_mata_kuliah_id',
                            $cpmk->pivot->sub_penilaian_cpmk_mata_kuliah_id
                        )
                        ->avg('nilai_terbobot');

                    return [
                        'kode_cpl'              => $cpmk->cpls->first()->kode_cpl ?? null,
                        'kode_cpmk'             => $cpmk->kode_cpmk,
                        'sub_penilaian'        => $subPenilaian->nama_sub_penilaian,
                        'bobot'                 => $cpmk->pivot->bobot . '%',
                        'rerata_skor_mahasiswa' => round($rerata, 2),
                    ];
                });
            })
            ->values()
            ->toArray();

        // Statistik % mahasiswa yang mencapai CPMK ≥ 80
        $totalMahasiswa = $kelas->mahasiswas->count();
        $mahasiswaMencapaiSkorMemuaskan = [];
        foreach ($subPenilaianCpmks as $cpmk) {
            // cari semua pivot sub_penilaian_cpmk_mata_kuliah untuk cpmk ini
            $pivotIds = $kelas->subPenilaian
                ->flatMap(fn($subPenilaian) => $subPenilaian->cpmks
                    ->where('cpmk_id', $cpmk->cpmk_id)
                    ->pluck('pivot.sub_penilaian_cpmk_mata_kuliah_id'))
                ->unique()
                ->toArray();

            $passed = DB::table('nilai_sub_penilaian_mahasiswa')
                ->select('mahasiswa_id', DB::raw('SUM(nilai_terbobot) as total'))
                ->whereIn('sub_penilaian_cpmk_mata_kuliah_id', $pivotIds)
                ->groupBy('mahasiswa_id')
                ->having('total', '>=', 80)
                ->get()
                ->count();

            $mahasiswaMencapaiSkorMemuaskan[] = [
                'cpmk_id'   => $cpmk->cpmk_id,
                'kode_cpmk' => $cpmk->kode_cpmk,
                'percentage' => $totalMahasiswa
                    ? round($passed * 100 / $totalMahasiswa, 2) . '%'
                    : '0%',
            ];
        }

        return [
            'header'                => $header,
            'daftar_penilaian'      => $daftarPenilaian,
            'pemetaan_cpl_cpmk'      => $pemetaanCplCpmk,
            'pemetaan_dan_nilai'    => $pemetaanDanNilai,
            'mahasiswa_mencapai_skor_memuaskan'        => $mahasiswaMencapaiSkorMemuaskan,
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
