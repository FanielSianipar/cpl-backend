<?php

namespace App\Services;

use App\Models\CPMK;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Collection;

class AdminUniversitasService
{
    // Data Fakultas
    public function dataFakultas(): array
    {
        try {
            // Ambil data fakultas dari model Fakultas
            $fakultas = Fakultas::select('fakultas_id', 'kode_fakultas', 'nama_fakultas')->get();
            return [
                'data'    => $fakultas,
                'message' => 'Data fakultas berhasil diambil.'
            ];
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data fakultas: ' . $e->getMessage());
        }
    }

    /**
     * Kelola data prodi melalui model User.
     * Operasi yang didukung: view, store, update, dan delete.
     * Hanya user dengan role 'admin universitas' yang akan diproses.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function kelolaDataProdi(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // Jika terdapat parameter id, ambil detail satu data Prodi beserta relasi Fakultas.
                    if (isset($data['prodi_id'])) {
                        $prodi = Prodi::with('fakultas')->findOrFail($data['prodi_id']);
                        return [
                            'data'    => $prodi,
                            'message' => 'Data prodi berhasil diambil.'
                        ];
                    } else {
                        // Jika tidak ada parameter id, ambil semua data Prodi beserta relasi Fakultas.
                        $prodis = Prodi::with('fakultas')->get();
                        return [
                            'data'    => $prodis,
                            'message' => 'Semua data prodi berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    DB::beginTransaction();
                    // Membuat data prodi baru menggunakan Eloquent ORM
                    $prodi = Prodi::create([
                        'kode_prodi'  => $data['kode_prodi'],
                        'nama_prodi'  => $data['nama_prodi'],
                        'fakultas_id' => $data['fakultas_id'],
                    ]);
                    DB::commit();
                    return [
                        'data'    => $prodi,
                        'message' => 'Data prodi berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['prodi_id'])) {
                        return ['message' => 'ID prodi tidak ditemukan untuk update.'];
                    }
                    DB::beginTransaction();
                    $prodi = Prodi::findOrFail($data['prodi_id']);
                    $prodi->update([
                        'kode_prodi'  => $data['kode_prodi']  ?? $prodi->kode_prodi,
                        'nama_prodi'  => $data['nama_prodi']  ?? $prodi->nama_prodi,
                        'fakultas_id' => $data['fakultas_id'] ?? $prodi->fakultas_id,
                    ]);
                    DB::commit();
                    return [
                        'data'    => $prodi,
                        'message' => 'Data prodi berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    if (!isset($data['prodi_id'])) {
                        return ['message' => 'ID prodi tidak ditemukan untuk dihapus.'];
                    }
                    DB::beginTransaction();
                    $prodi = Prodi::findOrFail($data['prodi_id']);
                    $prodi->delete();
                    DB::commit();
                    return [
                        'message' => 'Data prodi berhasil dihapus.'
                    ];
                    break;

                default:
                    return [
                        'message' => 'Aksi tidak diketahui.'
                    ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Kelola akun Admin Universitas melalui model User.
     * Operasi yang didukung: view, store, update, dan delete.
     * Hanya user dengan role 'admin universitas' yang akan diproses.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */

    public function kelolaAkunAdminUniversitas(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    if (isset($data['id'])) {
                        $user = User::role('Admin Universitas')
                            ->with('prodi')
                            ->select('id', 'name', 'email', 'nip')
                            ->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Admin Universitas berhasil diambil.'
                        ];
                    } else {
                        $users = User::role('Admin Universitas')
                            ->where('id', '!=', auth()->id())
                            ->with('prodi')
                            ->select('id', 'name', 'email', 'nip')
                            ->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Admin Universitas berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    DB::beginTransaction();

                    // Buat user baru dengan Eloquent ORM.
                    $user = User::create([
                        'name'     => $data['name'],
                        'email'    => $data['email'],
                        'nip'      => $data['nip'],
                        'password' => bcrypt($data['password']),
                        'remember_token' => Str::random(10),
                    ]);

                    // Assign role 'Admin Universitas'
                    $user->assignRole('Admin Universitas');

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Universitas berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk update.'];
                    }

                    DB::beginTransaction();

                    $user = User::role('Admin Universitas')->findOrFail($data['id']);
                    $user->update([
                        'name'     => $data['name']  ?? $user->name,
                        'email'    => $data['email'] ?? $user->email,
                        'nip'      => $data['nip']   ?? $user->nip,
                        'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
                    ]);

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Universitas berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    $user = User::role('Admin Universitas')->findOrFail($data['id']);
                    $user->delete();

                    DB::commit();

                    return [
                        'message' => 'Akun Admin Universitas berhasil dihapus.'
                    ];
                    break;

                default:
                    throw new Exception('Aksi tidak valid atau belum disediakan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kelola akun Admin Prodi melalui model User.
     * Operasi yang didukung: view, store, update, dan delete.
     * Hanya user dengan role 'admin universitas' yang akan diproses.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function kelolaAkunAdminProdi(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    if (isset($data['id'])) {
                        $user = User::role('Admin Prodi')
                            ->with('prodi')
                            ->with('prodi.fakultas')
                            ->select('id', 'name', 'email', 'nip', 'prodi_id')
                            ->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Admin Prodi berhasil diambil.'
                        ];
                    } else {
                        $users = User::role('Admin Prodi')
                            ->where('id', '!=', auth()->id())
                            ->with('prodi')
                            ->with('prodi.fakultas')
                            ->select('id', 'name', 'email', 'nip', 'prodi_id')
                            ->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Admin Prodi berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    DB::beginTransaction();

                    // Buat user baru
                    $user = User::create([
                        'name'           => $data['name'],
                        'email'          => $data['email'],
                        'nip'            => $data['nip'],
                        'password'       => bcrypt($data['password']),
                        'remember_token' => Str::random(10),
                        'prodi_id'       => $data['prodi_id'],
                    ]);

                    // Assign role "Admin Prodi"
                    $user->assignRole('Admin Prodi');

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Prodi berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk update.'];
                    }

                    DB::beginTransaction();

                    $user = User::role('Admin Prodi')->findOrFail($data['id']);
                    $user->update([
                        'name'     => $data['name'] ?? $user->name,
                        'email'    => $data['email'] ?? $user->email,
                        'nip'      => $data['nip'] ?? $user->nip,
                        'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
                        'prodi_id' => $data['prodi_id'] ?? $user->prodi_id, // update jika diberikan
                    ]);

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Prodi berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk delete.'];
                    }

                    DB::beginTransaction();

                    $user = User::role('Admin Prodi')->findOrFail($data['id']);
                    $user->delete();

                    DB::commit();

                    return [
                        'message' => 'Akun Admin Prodi berhasil dihapus.'
                    ];
                    break;

                default:
                    throw new Exception('Aksi tidak valid atau belum disediakan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function statusPengisianNilaiCplProdi(): array
    {
        $query = Prodi::select(['kode_prodi', 'nama_prodi', 'fakultas_id'])
            ->with(['fakultas:fakultas_id,nama_fakultas'])
            ->get();

        return $query->map(function ($query) {

            // $status = logika untuk menentukan status pengisian nilai CPL Prodi, misalnya dengan mengecek apakah semua kelas di prodi tersebut sudah mengisi nilai CPL atau belum.

            return [
                'kode_prodi' => $query->kode_prodi,
                'nama_prodi'  => $query->nama_prodi,
                'nama_fakultas'       => $query->fakultas->nama_fakultas,
                'status'      => 'Belum Selesai' // Placeholder, logika status bisa ditambahkan sesuai kebutuhan
            ];
        })->toArray();
    }

    public function daftarProdi(): array
    {
        $query = Prodi::select(['kode_prodi', 'nama_prodi', 'fakultas_id'])
            ->with(['fakultas:fakultas_id,nama_fakultas'])
            ->get();

        return $query->map(function ($query) {
            return [
                'kode_prodi' => $query->kode_prodi,
                'nama_prodi'  => $query->nama_prodi,
                'nama_fakultas'       => $query->fakultas->nama_fakultas,
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
}
