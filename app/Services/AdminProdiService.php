<?php

namespace App\Services;

use App\Models\CPL;
use App\Models\CplMataKuliah;
use App\Models\CPMK;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\MataKuliahCpmkPivot;
use App\Models\Prodi;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminProdiService
{
    protected SubPenilaianService $subPenilaianService;

    public function __construct(SubPenilaianService $subPenilaianService)
    {
        $this->subPenilaianService = $subPenilaianService;
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
    public function kelolaAkunKaprodi(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    if (isset($data['id'])) {
                        // Ambil data satu akun Kaprodi berdasarkan ID
                        $user = User::role('Kaprodi')
                            ->with('prodi')
                            ->with('prodi.fakultas')
                            ->select('id', 'name', 'email', 'nip', 'prodi_id')
                            ->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Kaprodi berhasil diambil.'
                        ];
                    } else {
                        // Ambil semua akun Kaprodi
                        $users = User::role('Kaprodi')
                            ->with('prodi')
                            ->with('prodi.fakultas')
                            ->select('id', 'name', 'email', 'nip', 'prodi_id')
                            ->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Kaprodi berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    DB::beginTransaction();

                    // Buat user baru dengan Eloquent ORM
                    $user = User::create([
                        'name'           => $data['name'],
                        'email'          => $data['email'],
                        'nip'            => $data['nip'],
                        'password'       => bcrypt($data['password']),
                        'remember_token' => Str::random(10),
                        'prodi_id'       => auth()->user()->prodi_id,
                    ]);

                    // Assign role "Kaprodi"
                    $user->assignRole('Kaprodi');

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Kaprodi berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk update.'];
                    }

                    DB::beginTransaction();

                    // Ambil data akun Kaprodi yang akan diperbarui
                    $user = User::role('Kaprodi')->findOrFail($data['id']);
                    $user->update([
                        'name'     => $data['name'] ?? $user->name,
                        'email'    => $data['email'] ?? $user->email,
                        'nip'      => $data['nip'] ?? $user->nip,
                        'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
                    ]);

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Kaprodi berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    // Hapus akun Kaprodi berdasarkan ID
                    $user = User::role('Kaprodi')->findOrFail($data['id']);
                    $user->delete();

                    DB::commit();

                    return [
                        'message' => 'Akun Kaprodi berhasil dihapus.'
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

    public function kelolaAkunDosen(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    if (isset($data['id'])) {
                        // Ambil data satu akun Dosen berdasarkan ID
                        $user = User::role('Dosen')
                            ->with('prodi')
                            ->with('prodi.fakultas')
                            ->select('id', 'name', 'email', 'nip', 'prodi_id')
                            ->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Dosen berhasil diambil.'
                        ];
                    } else {
                        // Ambil semua akun Dosen
                        $users = User::role('Dosen')
                            ->with('prodi')
                            ->with('prodi.fakultas')
                            ->select('id', 'name', 'email', 'nip', 'prodi_id')
                            ->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Dosen berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    DB::beginTransaction();

                    // Buat user baru dengan Eloquent ORM
                    $user = User::create([
                        'name'           => $data['name'],
                        'email'          => $data['email'],
                        'nip'            => $data['nip'],
                        'password'       => bcrypt($data['password']),
                        'remember_token' => Str::random(10),
                        'prodi_id'       => auth()->user()->prodi_id,
                    ]);

                    // Assign role "Dosen"
                    $user->assignRole('Dosen');

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Dosen berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk update.'];
                    }

                    DB::beginTransaction();

                    // Ambil data akun Dosen yang akan diperbarui
                    $user = User::role('Dosen')->findOrFail($data['id']);
                    $user->update([
                        'name'     => $data['name'] ?? $user->name,
                        'email'    => $data['email'] ?? $user->email,
                        'nip'      => $data['nip'] ?? $user->nip,
                        'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
                    ]);

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Dosen berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    // Hapus akun Dosen berdasarkan ID
                    $user = User::role('Dosen')->findOrFail($data['id']);
                    $user->delete();

                    DB::commit();

                    return [
                        'message' => 'Akun Dosen berhasil dihapus.'
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

    public function kelolaDataMahasiswa(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // Jika terdapat parameter id, ambil detail satu data mahasiswa.
                    if (isset($data['mahasiswa_id'])) {
                        $mahasiswa = Mahasiswa::with(['Prodi' => function ($query) {
                            $query->select('prodi_id', 'kode_prodi', 'nama_prodi');
                        }])
                            ->select('mahasiswa_id', 'npm', 'name', 'angkatan', 'prodi_id')
                            ->findOrFail($data['mahasiswa_id']);
                        return [
                            'data'    => $mahasiswa,
                            'message' => 'Data mahasiswa berhasil diambil.'
                        ];
                    } else {
                        $mahasiswas = Mahasiswa::with(['prodi' => function ($query) {
                            $query->select('prodi_id', 'kode_prodi', 'nama_prodi');
                        }])
                            ->select('mahasiswa_id', 'npm', 'name', 'angkatan', 'prodi_id')
                            ->get();
                        return [
                            'data'    => $mahasiswas,
                            'message' => 'Semua data mahasiswa berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah data Mahasiswa baru.
                    DB::beginTransaction();
                    $mahasiswa = Mahasiswa::create([
                        'npm'      => $data['npm'],
                        'name'     => $data['name'],
                        'angkatan' => $data['angkatan'],
                        'prodi_id' => auth()->user()->prodi_id,
                    ]);
                    DB::commit();

                    return [
                        'data'    => $mahasiswa,
                        'message' => 'Data mahasiswa berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['mahasiswa_id'])) {
                        return ['message' => 'ID mahasiswa tidak ditemukan untuk update.'];
                    }

                    // Perbarui data mahasiswa.
                    DB::beginTransaction();
                    $mahasiswa = Mahasiswa::with('prodi')->findOrFail($data['mahasiswa_id']);

                    // Ambil prodi_id dari request atau gunakan yang sudah ada
                    $prodi_id = $data['prodi_id'] ?? $mahasiswa->prodi->prodi_id;

                    // Pastikan prodi yang dikirimkan ada dalam database sebelum update
                    if (!Prodi::where('prodi_id', $prodi_id)->exists()) {
                        return ['message' => 'Prodi yang diberikan tidak valid atau tidak ditemukan.'];
                    }

                    $mahasiswa->update([
                        'npm'     => $data['npm']     ?? $mahasiswa->npm,
                        'name'     => $data['name']     ?? $mahasiswa->name,
                        'angkatan' => $data['angkatan'] ?? $mahasiswa->angkatan,
                        'prodi_id' => $prodi_id
                    ]);
                    $mahasiswa = $mahasiswa->fresh('prodi');

                    DB::commit();

                    return [
                        'data'    => $mahasiswa,
                        'message' => 'Data mahasiswa berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus data mahasiswa berdasarkan id.
                    if (!isset($data['mahasiswa_id'])) {
                        return ['message' => 'ID mahasiswa tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    $mahasiswa = Mahasiswa::findOrFail($data['mahasiswa_id']);
                    $mahasiswa->delete();
                    DB::commit();
                    return [
                        'message' => 'Data mahasiswa berhasil dihapus.'
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

    public function kelolaDataMataKuliah(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // Jika terdapat parameter id, ambil detail satu data mata kuliah.
                    if (isset($data['mata_kuliah_id'])) {
                        $mataKuliah = MataKuliah::with(['Prodi' => function ($query) {
                            $query->select('prodi_id', 'kode_prodi', 'nama_prodi');
                        }])
                            ->select('mata_kuliah_id', 'kode_mata_kuliah', 'nama_mata_kuliah', 'prodi_id')
                            ->findOrFail($data['mata_kuliah_id']);
                        return [
                            'data'    => $mataKuliah,
                            'message' => 'Data mata kuliah berhasil diambil.'
                        ];
                    } else {
                        $mataKuliahs = MataKuliah::with(['prodi' => function ($query) {
                            $query->select('prodi_id', 'kode_prodi', 'nama_prodi');
                        }])
                            ->select('mata_kuliah_id', 'kode_mata_kuliah', 'nama_mata_kuliah', 'prodi_id')
                            ->get();
                        return [
                            'data'    => $mataKuliahs,
                            'message' => 'Semua data mata kuliah berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah data Mata Kuliah baru.
                    DB::beginTransaction();
                    $mataKuliah = MataKuliah::create([
                        'kode_mata_kuliah'      => $data['kode_mata_kuliah'],
                        'nama_mata_kuliah'     => $data['nama_mata_kuliah'],
                        'prodi_id' => auth()->user()->prodi_id,
                    ]);
                    DB::commit();

                    return [
                        'data'    => $mataKuliah,
                        'message' => 'Data mata kuliah berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['mata_kuliah_id'])) {
                        return ['message' => 'ID mata kuliah tidak ditemukan untuk update.'];
                    }

                    // Perbarui data mata_kuliah.
                    DB::beginTransaction();
                    $mataKuliah = MataKuliah::with('prodi')->findOrFail($data['mata_kuliah_id']);

                    $mataKuliah->update([
                        'kode_mata_kuliah'     => $data['kode_mata_kuliah']     ?? $mataKuliah->kode_mata_kuliah,
                        'nama_mata_kuliah'     => $data['nama_mata_kuliah']     ?? $mataKuliah->nama_mata_kuliah,
                    ]);
                    $mataKuliah = $mataKuliah->fresh('prodi');

                    DB::commit();

                    return [
                        'data'    => $mataKuliah,
                        'message' => 'Data mata kuliah berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus data mata kuliah berdasarkan id.
                    if (!isset($data['mata_kuliah_id'])) {
                        return ['message' => 'ID mata kuliah tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    $mataKuliah = MataKuliah::findOrFail($data['mata_kuliah_id']);
                    $mataKuliah->delete();
                    DB::commit();
                    return [
                        'message' => 'Data mata kuliah berhasil dihapus.'
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

    public function kelolaDataKelas(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    $kelasQuery = Kelas::with([
                        // Ambil mata_kuliah dengan kolom spesifik
                        'mataKuliah:mata_kuliah_id,kode_mata_kuliah,nama_mata_kuliah',
                        // Ambil dosen dengan kolom id,name dan pivot.jabatan
                        'dosens:id,name'
                    ])
                        // Select hanya kolom dari tabel kelas
                        ->select('kelas_id', 'kode_kelas', 'nama_kelas', 'semester', 'tahun_ajaran', 'mata_kuliah_id');

                    if (isset($data['kelas_id'])) {
                        $kelas = $kelasQuery->findOrFail($data['kelas_id']);
                        return [
                            'data'    => $kelas,
                            'message' => 'Data kelas berhasil diambil.'
                        ];
                    }

                    $all = $kelasQuery->get();
                    return [
                        'data'    => $all,
                        'message' => 'Semua data kelas berhasil diambil.'
                    ];

                    break;

                case 'store':
                    // Tambah data Kelas baru.
                    DB::beginTransaction();
                    $kelas = Kelas::create([
                        'kode_kelas'      => $data['kode_kelas'],
                        'nama_kelas'     => $data['nama_kelas'],
                        'semester'     => $data['semester'],
                        'tahun_ajaran'     => $data['tahun_ajaran'],
                        'mata_kuliah_id' => $data['mata_kuliah_id'],
                    ]);

                    // menambahkan dosen pengampu ke kelas
                    if (!empty($data['dosens']) && is_array($data['dosens'])) {
                        $sync = [];
                        foreach ($data['dosens'] as $dosen) {
                            // validasi jabatan dan dosen_id bisa ditambahkan di sini
                            $sync[$dosen['dosen_id']] = ['jabatan' => $dosen['jabatan']];
                        }
                        $kelas->dosens()->sync($sync);
                    }

                    DB::commit();

                    return [
                        'data'    => $kelas->load('mataKuliah', 'dosens'),
                        'message' => 'Data kelas berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['kelas_id'])) {
                        return ['message' => 'ID kelas tidak ditemukan untuk update.'];
                    }

                    // Perbarui data kelas.
                    DB::beginTransaction();
                    $kelas = Kelas::with('mataKuliah')->findOrFail($data['kelas_id']);

                    // Ambil mata_kuliah_id dari request atau gunakan yang sudah ada
                    $mataKuliahId = $data['mata_kuliah_id'] ?? $kelas->mataKuliah->mata_kuliah_id;

                    // Pastikan mata kuliah yang dikirimkan ada dalam database sebelum update
                    if (!MataKuliah::where('mata_kuliah_id', $mataKuliahId)->exists()) {
                        return ['message' => 'Mata kuliah yang diberikan tidak valid atau tidak ditemukan.'];
                    }

                    $kelas->update([
                        'kode_kelas'     => $data['kode_kelas']     ?? $kelas->kode_kelas,
                        'nama_kelas'     => $data['nama_kelas']     ?? $kelas->nama_kelas,
                        'semester'     => $data['semester']     ?? $kelas->semester,
                        'tahun_ajaran'     => $data['tahun_ajaran']     ?? $kelas->tahun_ajaran,
                        'mata_kuliah_id' => $mataKuliahId
                    ]);

                    if (isset($data['dosens']) && is_array($data['dosens'])) {
                        $sync = [];
                        foreach ($data['dosens'] as $dosen) {
                            $sync[$dosen['dosen_id']] = ['jabatan' => $dosen['jabatan']];
                        }
                        // sync = replace; syncWithoutDetaching = tambah tanpa menghapus
                        $kelas->dosens()->sync($sync);
                    }

                    DB::commit();

                    $kelas = $kelas->load('mataKuliah', 'dosens');

                    return [
                        'data'    => $kelas,
                        'message' => 'Data kelas berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus data kelas berdasarkan id.
                    if (!isset($data['kelas_id'])) {
                        return ['message' => 'ID kelas tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    $kelas = Kelas::findOrFail($data['kelas_id']);
                    $kelas->delete();
                    DB::commit();
                    return [
                        'message' => 'Data kelas berhasil dihapus.'
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

    public function daftarMahasiswaKeKelas(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // 1. Detail seorang mahasiswa
                    if (!empty($data['mahasiswa_id'])) {
                        $mahasiswa = Mahasiswa::with('kelasDiikuti')
                            ->findOrFail($data['mahasiswa_id']);

                        return [
                            'data'    => $mahasiswa,
                            'message' => 'Detail mahasiswa berhasil diambil.'
                        ];
                    }

                    // 2. Daftar semua mahasiswa di satu kelas
                    if (!empty($data['kelas_id'])) {
                        $kelas = Kelas::with([
                            'mahasiswas' => function ($q) {
                                // pastikan kolom dipilih dari tabel mahasiswa
                                $q->select([
                                    'mahasiswa.mahasiswa_id',
                                    'mahasiswa.npm',
                                    'mahasiswa.name',
                                    'mahasiswa.angkatan',
                                    'mahasiswa.prodi_id'
                                ]);
                            }
                        ])->findOrFail($data['kelas_id']);

                        return [
                            'data'    => $kelas->mahasiswas,
                            'message' => 'Daftar mahasiswa di kelas berhasil diambil.'
                        ];
                    }

                    throw new \InvalidArgumentException(
                        'Untuk view, sertakan mahasiswa_id atau kelas_id.'
                    );

                case 'store':
                    $kelas = Kelas::findOrFail($data['kelas_id']);

                    // Mulai transaksi untuk konsistensi pivot
                    $results = DB::transaction(function () use ($data, $kelas) {
                        $mahasiswaBaru = [];

                        foreach ($data['mahasiswas'] as $item) {
                            // Cek apakah sudah ada di tabel mahasiswa
                            $mahasiswa = Mahasiswa::where('npm', $item['npm'])->first();

                            if (!$mahasiswa) {
                                $angkatan = substr($item['npm'], 0, 2);

                                $mahasiswa = Mahasiswa::create([
                                    'npm'      => $item['npm'],
                                    'name'     => $item['name'],
                                    'angkatan' => $angkatan,
                                    'prodi_id' => auth()->user()->prodi_id,
                                ]);
                            }

                            // Attach ke kelas hanya jika belum terdaftar
                            if (!$kelas->mahasiswas()
                                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                                ->exists()) {
                                $kelas->mahasiswas()->attach($mahasiswa->mahasiswa_id);
                            }

                            $mahasiswaBaru[] = $mahasiswa->load('kelasDiikuti');
                        }

                        return $mahasiswaBaru;
                    });

                    return [
                        'data'    => $results,
                        'message' => 'Mahasiswa berhasil didaftarkan.'
                    ];

                case 'update':
                    // Update data mahasiswa
                    $mahasiswa = Mahasiswa::findOrFail($data['mahasiswa_id']);
                    $npm  = $data['npm'] ?? $mahasiswa->npm;

                    $mahasiswa->update([
                        'npm'      => $npm,
                        'name'     => $data['name']      ?? $mahasiswa->name,
                        'angkatan' => substr($npm, 0, 2),
                    ]);

                    // Opsional register ke kelas baru
                    if (!empty($data['kelas_id'])) {
                        $kelas = Kelas::findOrFail($data['kelas_id']);
                        $kelas->mahasiswas()
                            ->syncWithoutDetaching([$mahasiswa->mahasiswa_id]);
                    }

                    return [
                        'data'    => $mahasiswa->load('kelasDiikuti'),
                        'message' => 'Data mahasiswa berhasil diperbarui.'
                    ];

                case 'delete':
                    // 1. Validasi parameter
                    if (empty($data['kelas_id']) || empty($data['mahasiswa_id'])) {
                        return ['message' => 'Parameter kelas_id dan mahasiswa_id diperlukan untuk delete.'];
                    }

                    // 2. Load Kelas
                    $kelas = Kelas::findOrFail($data['kelas_id']);

                    // 3. Cek apakah mahasiswa masih terdaftar
                    $terdaftar = $kelas->mahasiswas()
                        ->wherePivot('mahasiswa_id', $data['mahasiswa_id'])
                        ->exists();

                    if (!$terdaftar) {
                        return [
                            'message' => 'Mahasiswa tidak terdaftar di kelas ini.'
                        ];
                    }

                    // 4. Detach pivot
                    $kelas->mahasiswas()->detach($data['mahasiswa_id']);

                    return [
                        'message' => 'Mahasiswa berhasil dikeluarkan dari kelas.'
                    ];

                default:
                    throw new \BadMethodCallException("Aksi tidak valid: {$action}");
            }
        } catch (\Exception $e) {
            return ['message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function kelolaDataCpl(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // Jika terdapat parameter id, ambil detail satu data CPL.
                    if (isset($data['cpl_id'])) {
                        $cpl = CPL::with(['Prodi' => function ($query) {
                            $query->select('prodi_id', 'kode_prodi', 'nama_prodi');
                        }])
                            ->select('cpl_id', 'kode_cpl', 'nama_cpl', 'deskripsi', 'prodi_id')
                            ->findOrFail($data['cpl_id']);
                        return [
                            'data'    => $cpl,
                            'message' => 'Data CPL berhasil diambil.'
                        ];
                    } else {
                        $cpls = CPL::with(['prodi' => function ($query) {
                            $query->select('prodi_id', 'kode_prodi', 'nama_prodi');
                        }])
                            ->select('cpl_id', 'kode_cpl', 'nama_cpl', 'deskripsi', 'prodi_id')
                            ->get();
                        return [
                            'data'    => $cpls,
                            'message' => 'Semua data CPL berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah data CPL baru.
                    DB::beginTransaction();
                    $cpl = CPL::create([
                        'kode_cpl'      => $data['kode_cpl'],
                        'nama_cpl'     => $data['nama_cpl'],
                        'deskripsi'     => $data['deskripsi'],
                        'prodi_id' => auth()->user()->prodi_id,
                    ]);
                    DB::commit();

                    return [
                        'data'    => $cpl,
                        'message' => 'Data CPL berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['cpl_id'])) {
                        return ['message' => 'ID CPL tidak ditemukan untuk update.'];
                    }

                    // Perbarui data CPL.
                    DB::beginTransaction();
                    $cpl = CPL::with('prodi')->findOrFail($data['cpl_id']);

                    $cpl->update([
                        'kode_cpl'     => $data['kode_cpl']     ?? $cpl->kode_cpl,
                        'nama_cpl'     => $data['nama_cpl']     ?? $cpl->nama_cpl,
                        'deskripsi'     => $data['deskripsi']     ?? $cpl->deskripsi,
                    ]);
                    $cpl = $cpl->fresh('prodi');

                    DB::commit();

                    return [
                        'data'    => $cpl,
                        'message' => 'Data CPL berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus data CPL berdasarkan id.
                    if (!isset($data['cpl_id'])) {
                        return ['message' => 'ID CPL tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    $cpl = CPL::findOrFail($data['cpl_id']);
                    $cpl->delete();
                    DB::commit();
                    return [
                        'message' => 'Data CPL berhasil dihapus.'
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

    public function kelolaDataCpmk(array $data): array
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // Jika terdapat parameter id, ambil detail satu data CPMK.
                    if (isset($data['cpmk_id'])) {
                        $cpmk = CPMK::with(['mataKuliah' => function ($query) {
                            $query->select('mata_kuliah_id', 'kode_mata_kuliah', 'nama_mata_kuliah');
                        }])
                            ->select('cpmk_id', 'kode_cpmk', 'nama_cpmk', 'deskripsi', 'mata_kuliah_id')
                            ->findOrFail($data['cpmk_id']);
                        return [
                            'data'    => $cpmk,
                            'message' => 'Data CPMK berhasil diambil.'
                        ];
                    } else {
                        $cpmks = CPMK::with(['mataKuliah' => function ($query) {
                            $query->select('mata_kuliah_id', 'kode_mata_kuliah', 'nama_mata_kuliah');
                        }])
                            ->select('cpmk_id', 'kode_cpmk', 'nama_cpmk', 'deskripsi', 'mata_kuliah_id')
                            ->get();
                        return [
                            'data'    => $cpmks,
                            'message' => 'Semua data CPMK berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah data CPMK baru.
                    DB::beginTransaction();
                    $cpmk = CPMK::create([
                        'kode_cpmk'      => $data['kode_cpmk'],
                        'nama_cpmk'     => $data['nama_cpmk'],
                        'deskripsi'     => $data['deskripsi'],
                        'mata_kuliah_id' => $data['mata_kuliah_id'],
                    ]);
                    DB::commit();

                    return [
                        'data'    => $cpmk,
                        'message' => 'Data CPMK berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['cpmk_id'])) {
                        return ['message' => 'ID CPMK tidak ditemukan untuk update.'];
                    }

                    // Perbarui data CPMK.
                    DB::beginTransaction();
                    $cpmk = CPMK::with('mataKuliah')->findOrFail($data['cpmk_id']);

                    $cpmk->update([
                        'kode_cpmk'     => $data['kode_cpmk']     ?? $cpmk->kode_cpmk,
                        'nama_cpmk'     => $data['nama_cpmk']     ?? $cpmk->nama_cpmk,
                        'deskripsi'     => $data['deskripsi']     ?? $cpmk->deskripsi,
                        'mata_kuliah_id' => $data['mata_kuliah_id'] ?? $cpmk->mata_kuliah_id,
                    ]);
                    $cpmk = $cpmk->fresh('mataKuliah');

                    DB::commit();

                    return [
                        'data'    => $cpmk,
                        'message' => 'Data CPMK berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus data CPMK berdasarkan id.
                    if (!isset($data['cpmk_id'])) {
                        return ['message' => 'ID CPMK tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

                    $cpmk = CPMK::findOrFail($data['cpmk_id']);
                    $cpmk->delete();
                    DB::commit();
                    return [
                        'message' => 'Data CPMK berhasil dihapus.'
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
     * Mengelola pemetaan CPL pada sebuah mata kuliah.
     *
     * Format input pada $data:
     * [
     *   'action' => 'store'|'view'|'update'|'delete',
     *   'mata_kuliah_id' => (int),
     *   // Untuk store dan update:
     *   'cpls' => [
     *         [ 'cpl_id' => (int), 'bobot' => (float) ],
     *         ...
     *   ]
     * ]
     *
     * @param  array  $data
     * @return array
     */
    public function pemetaanCpl(array $data): array
    {
        if (!isset($data['action'])) {
            return ['message' => 'Action tidak ditemukan.'];
        }

        switch ($data['action']) {
            case 'view':
                // Jika ada ID mata kuliah, tampilkan pemetaan CPL untuk mata kuliah tersebut.
                if (isset($data['mata_kuliah_id'])) {
                    $mataKuliah = MataKuliah::with('cpls')->find($data['mata_kuliah_id']);
                    if (!$mataKuliah) {
                        return ['message' => 'Mata kuliah tidak ditemukan.'];
                    }
                    return [
                        'data'    => $mataKuliah->cpls,
                        'message' => 'Data pemetaan CPL berhasil diambil.'
                    ];
                }
                return ['message' => 'ID mata kuliah diperlukan untuk aksi view.'];
                break;

            case 'store':
                // Untuk create, validasi adanya data dan cek total bobot harus 100%
                if (!isset($data['mata_kuliah_id']) || !isset($data['cpls'])) {
                    return ['message' => 'Mata kuliah dan data CPL harus disertakan untuk aksi store.'];
                }
                return $this->syncPemetaanCpl($data['mata_kuliah_id'], $data['cpls'], 'store');
                break;

            case 'update':
                // Untuk update, kita lakukan sinkronisasi ulang dengan data baru.
                if (!isset($data['mata_kuliah_id']) || !isset($data['cpls'])) {
                    return ['message' => 'Mata kuliah dan data CPL harus disertakan untuk aksi update.'];
                }
                return $this->syncPemetaanCpl($data['mata_kuliah_id'], $data['cpls'], 'update');
                break;

            case 'delete':
                // Untuk delete, keluarkan seluruh mapping CPL untuk mata kuliah tertentu.
                if (!isset($data['cpl_mata_kuliah_id'])) {
                    return ['message' => 'ID pemetaan CPL diperlukan untuk aksi delete.'];
                }
                try {
                    DB::beginTransaction();
                    $cplMataKuliah = CplMataKuliah::findOrFail($data['cpl_mata_kuliah_id']);
                    $cplMataKuliah->delete();
                    DB::commit();
                    return ['message' => 'Pemetaan CPL berhasil dihapus.'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    return ['message' => 'Pemetaan CPL gagal dihapus: ' . $e->getMessage()];
                }
                break;
            default:
                return ['message' => 'Action tidak dikenali.'];
        }
    }

    /**
     * Fungsi sinkronisasi pemetaan CPL.
     *
     * Melakukan validasi untuk memastikan total bobot CPL = 100%
     * dan kemudian melakukan sinkronisasi (create atau update) melalui relasi many-to-many.
     *
     * @param int   $mataKuliahId
     * @param array $cpls         Array berisi data CPL dengan key 'cpl_id' dan 'bobot'
     * @param string $mode        'store' atau 'update'
     * @return array
     */
    protected function syncPemetaanCpl(int $mataKuliahId, array $cpls, string $mode): array
    {
        DB::beginTransaction();
        try {
            $mataKuliah     = MataKuliah::with('cpls')->findOrFail($mataKuliahId);
            $existingTotal  = $mataKuliah->cpls->sum(fn($c) => $c->pivot->bobot);

            $syncData = [];
            $newTotal  = 0;
            foreach ($cpls as $item) {
                if (!isset($item['cpl_id'], $item['bobot'])) {
                    throw new \Exception('Data CPL tidak lengkap.');
                }
                $cplId = (int) $item['cpl_id'];
                $bobot = (float) $item['bobot'];
                $syncData[$cplId] = ['bobot' => $bobot];
                $newTotal += $bobot;
            }

            // --- Cek duplikat untuk mode store ---
            if ($mode === 'store') {
                // Ambil daftar cpl_id yang sudah ada
                $existingIds = $mataKuliah->cpls->pluck('cpl_id')->toArray();
                $inputIds    = array_keys($syncData);

                // Cari duplikasi
                $dupes = array_intersect($existingIds, $inputIds);
                if (!empty($dupes)) {
                    // Ambil nama CPL berdasarkan ID yang duplikat
                    $dupeNames = Cpl::whereIn('cpl_id', $dupes)
                        ->pluck('nama_cpl')
                        ->toArray();

                    // Format pesannya
                    $message = implode(', ', $dupeNames)
                        . ' sudah terdaftar pada mata kuliah ini.';

                    throw ValidationException::withMessages([
                        'cpls' => [$message]
                    ]);
                }
            }

            // --- Validasi total bobot maksimum 100% ---
            if ($mode === 'store') {
                $total = $existingTotal + $newTotal;
            } else {
                // Kurangi bobot lama untuk CPL yang di‐update
                $idsUpdating     = array_keys($syncData);
                $oldForUpdating  = $mataKuliah->cpls
                    ->whereIn('cpl_id', $idsUpdating)
                    ->sum(fn($c) => $c->pivot->bobot);
                $total = ($existingTotal - $oldForUpdating) + $newTotal;
            }

            if ($total > 100.0) {
                throw ValidationException::withMessages([
                    'cpls' => ['Total bobot CPL melebihi 100%']
                ]);
            }

            // Simpan mapping:
            if ($mode === 'store') {
                $mataKuliah->cpls()->attach($syncData);
            } else {
                // Bisa langsung sync juga: $mataKuliah->cpls()->sync($syncData);
                $mataKuliah->cpls()->detach(array_keys($syncData));
                $mataKuliah->cpls()->attach($syncData);
            }

            DB::commit();

            $mataKuliah->load('cpls');
            return [
                'data'    => $mataKuliah->cpls,
                'message' => "Pemetaan CPL berhasil " . ($mode === 'store' ? 'ditambahkan.' : 'diperbarui.')
            ];
        } catch (ValidationException $ve) {
            DB::rollBack();
            throw $ve;
        } catch (\Exception $e) {
            DB::rollBack();
            return ['message' => 'Pemetaan CPL gagal: ' . $e->getMessage()];
        }
    }


    /**
     * Mengelola pemetaan CPMK pada sebuah mata kuliah.
     *
     * Format input pada $data:
     * [
     *   'action' => 'store'|'view'|'update'|'delete',
     *   'mata_kuliah_id' => (int),
     *   // Untuk store dan update:
     *   'cpls' => [
     *         [ 'cpl_id' => (int), 'bobot' => (float) ],
     *         ...
     *   ]
     * ]
     *
     * @param  array  $data
     * @return array
     */
    public function pemetaanCpmk(array $data): array
    {
        switch ($data['action']) {
            case 'view':
                // mata_kuliah_id yang dikirimkan akan menampilkan semua cpmk yang dipetakan ke mata kuliah tersebut
                if (!isset($data['cpmk_id'])) {
                    $mataKuliah = MataKuliah::with('cpmks.cpls')->findOrFail($data['mata_kuliah_id']);
                    $pemetaan = [];

                    foreach ($mataKuliah->cpmks as $cpmk) {
                        foreach ($cpmk->cpls as $cpl) {
                            $pemetaan[] = [
                                'cpmk_mata_kuliah_id' => $cpl->pivot->cpmk_mata_kuliah_id,
                                'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
                                'cpmk_id'        => $cpmk->cpmk_id,
                                'cpl_id'         => $cpl->cpl_id,
                                'bobot'          => $cpl->pivot->bobot,
                            ];
                        }
                    }

                    return [
                        'data'    => $pemetaan,
                        'message' => 'Data pemetaan CPMK berhasil diambil.',
                    ];
                }
                // if (isset($data['mata_kuliah_id'])) {
                //     $mataKuliah = MataKuliah::with('cpmks')->find($data['mata_kuliah_id']);
                //     if (!$mataKuliah) {
                //         return ['message' => 'Mata kuliah tidak ditemukan.'];
                //     }
                //     return [
                //         'data'    => $mataKuliah->cpmks,
                //         'message' => 'Data pemetaan CPMK berhasil diambil.'
                //     ];
                // }
                // return ['message' => 'ID mata kuliah diperlukan untuk aksi view.'];
                // break;

            case 'store':
                return $this->syncPemetaanCpmk($data, 'store');
                // if (!isset($data['mata_kuliah_id']) || !isset($data['cpmks'])) {
                //     return ['message' => 'Mata kuliah dan data CPMK harus disertakan untuk aksi store.'];
                // }
                // return $this->syncPemetaanCpmk($data['mata_kuliah_id'], $data['cpmks'], 'store');
                // break;

            case 'update':
                return $this->syncPemetaanCpmk($data, 'update');
                // if (!isset($data['mata_kuliah_id']) || !isset($data['cpmks'])) {
                //     return ['message' => 'Mata kuliah dan data CPMK harus disertakan untuk aksi update.'];
                // }
                // return $this->syncPemetaanCpmk($data['mata_kuliah_id'], $data['cpmks'], 'update');
                // break;

            case 'delete':
                return $this->deletePemetaanCpmk($data);
                // if (!isset($data['mata_kuliah_id'])) {
                //     return ['message' => 'ID mata kuliah diperlukan untuk aksi delete.'];
                // }
                // try {
                //     DB::beginTransaction();
                //     $mataKuliah = MataKuliah::findOrFail($data['mata_kuliah_id']);

                //     // Dapatkan relasi untuk mengambil nama kolom foreign key secara dinamis
                //     $relation = $mataKuliah->cpmks();
                //     $foreignPivotKey = $relation->getForeignPivotKeyName();  // biasany "mata_kuliah_id"
                //     $relatedPivotKey = $relation->getRelatedPivotKeyName();    // biasanya "cpmk_id"

                //     if (isset($data['cpmks']) && is_array($data['cpmks'])) {
                //         foreach ($data['cpmks'] as $item) {
                //             if (!isset($item['cpmk_id']) || !isset($item['cpl_id'])) {
                //                 throw new \Exception('Data cpmks tidak lengkap untuk delete.');
                //             }
                //             // Secara eksplisit gunakan whereRaw untuk memastikan kondisi ketat
                //             $deleted = $relation->newPivotQuery()
                //                 ->where($foreignPivotKey, (int)$data['mata_kuliah_id'])
                //                 ->where($relatedPivotKey, (int)$item['cpmk_id'])
                //                 ->where('cpl_id', (int)$item['cpl_id'])
                //                 ->delete();
                //             // Opsional: periksa jika $deleted tidak sama dengan 1, bisa di-log sebagai peringatan.
                //         }
                //     } else {
                //         // Jika tidak ada field "cpmks", tidak bisa menghapus mapping untuk mata kuliah tersebut.
                //         return ['message' => 'Pemetaan CPMK gagal dihapus.'];
                //     }

                //     DB::commit();
                //     return ['message' => 'Pemetaan CPMK berhasil dihapus.'];
                // } catch (\Exception $e) {
                //     DB::rollBack();
                //     return ['message' => 'Pemetaan CPMK gagal dihapus: ' . $e->getMessage()];
                // }
                // break;

            default:
                return ['message' => 'Action tidak dikenali.'];
        }
    }

    /**
     * Fungsi sinkronisasi pemetaan CPMK.
     *
     * Melakukan validasi untuk memastikan total bobot CPMK = 100%
     * dan kemudian melakukan sinkronisasi (create atau update) melalui relasi many-to-many.
     *
     * @param int   $mataKuliahId
     * @param array $cpmks         Array berisi data CPMK dengan key 'cpmk_id' dan 'bobot'
     * @param string $mode        'store' atau 'update'
     * @return array
     */
    protected function syncPemetaanCpmk(array $data, string $mode): array
    {
        DB::beginTransaction();
        try {
            // 1. Ambil mata kuliah beserta bobot CPL-nya
            $mataKuliah = MataKuliah::with('cpls')->findOrFail($data['mata_kuliah_id']);

            // 2. Hitung total bobot per CPL dari payload
            $sumPerCpl = [];
            foreach ($data['cpmks'] as $item) {
                if (!isset($item['cpmk_id'], $item['cpl_id'], $item['bobot'])) {
                    throw new \Exception('Payload cpmks tidak lengkap.');
                }
                $cplId = (int)$item['cpl_id'];
                $sumPerCpl[$cplId] = ($sumPerCpl[$cplId] ?? 0) + (float)$item['bobot'];
            }

            // 3. Validasi sum bobot tidak melebihi bobot CPL
            foreach ($sumPerCpl as $cplId => $totalBobot) {
                $pivot = $mataKuliah->cpls->firstWhere('cpl_id', $cplId);
                if (!$pivot) {
                    throw ValidationException::withMessages([
                        'cpmks' => ["Mapping CPL untuk cpl_id {$cplId} tidak ditemukan."]
                    ]);
                }
                $bobotCpl = $pivot->pivot->bobot;
                if ($totalBobot > $bobotCpl) {
                    // Lempar ValidationException dengan pesan persis untuk test
                    throw ValidationException::withMessages([
                        'cpmks' => ["Total bobot CPMK untuk CPL {$cplId} melebihi bobot CPL yang ditetapkan."]
                    ]);
                }
            }

            // 4. Jika lolos, group per CPMK dan attach/detach
            $grouped = [];
            foreach ($data['cpmks'] as $item) {
                $cpmkId = (int)$item['cpmk_id'];
                $cplId  = (int)$item['cpl_id'];
                $grouped[$cpmkId][$cplId] = ['bobot' => (float)$item['bobot']];
            }

            $resultData = [];
            foreach ($grouped as $cpmkId => $syncData) {
                $cpmk = CPMK::where('mata_kuliah_id', $data['mata_kuliah_id'])
                    ->findOrFail($cpmkId);

                if ($mode === 'store') {
                    $cpmk->cpls()->attach($syncData);
                } else {
                    $cpmk->cpls()->detach(array_keys($syncData));
                    $cpmk->cpls()->attach($syncData);
                }

                // Tambahkan cpmk_mata_kuliah_id pada output
                $resultData[$cpmkId] = $cpmk->cpls
                    ->map(fn($cpl) => [
                        'cpmk_mata_kuliah_id' => $cpl->pivot->cpmk_mata_kuliah_id,
                        'mata_kuliah_id'      => $cpmk->mata_kuliah_id,
                        'cpmk_id'             => $cpl->pivot->cpmk_id,
                        'cpl_id'              => $cpl->pivot->cpl_id,
                        'bobot'               => $cpl->pivot->bobot,
                    ])
                    ->toArray();
            }

            DB::commit();

            return [
                'data'    => $resultData,
                'message' => "Pemetaan CPMK berhasil " . ($mode === 'store' ? 'ditambahkan' : 'diperbarui') . "."
            ];
        } catch (ValidationException $ve) {
            DB::rollBack();
            throw $ve;
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'message' => 'Pemetaan CPMK gagal: ' . $e->getMessage()
            ];
        }
    }

    protected function deletePemetaanCpmk(array $data): array
    {
        // Pastikan cpmk_mata_kuliah_id dikirim
        if (empty($data['cpmk_mata_kuliah_id'])) {
            return ['message' => 'ID pemetaan CPMK wajib disertakan untuk delete.'];
        }

        DB::beginTransaction();
        try {
            // Cari record pivot by primary key
            $pivot = MataKuliahCpmkPivot::findOrFail($data['cpmk_mata_kuliah_id']);

            // Hapus pivot tunggal
            $pivot->delete();

            DB::commit();

            return ['message' => 'Pemetaan CPMK berhasil dihapus.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'message' => 'Penghapusan pemetaan CPMK gagal: ' . $e->getMessage()
            ];
        }
    }

    // protected function syncPemetaanCpmk(int $mataKuliahId, array $cpmks, string $mode): array
    // {
    //     DB::beginTransaction();
    //     try {
    //         $mataKuliah = MataKuliah::findOrFail($mataKuliahId);
    //         $syncData = [];  // Array untuk menyiapkan data attach, key adalah cpmk_id
    //         $grouped   = []; // Mengelompokkan total bobot per CPL

    //         foreach ($cpmks as $item) {
    //             if (!isset($item['cpmk_id']) || !isset($item['cpl_id']) || !isset($item['bobot'])) {
    //                 throw new \Exception('Data CPMK tidak lengkap.');
    //             }
    //             $cplId = $item['cpl_id'];
    //             if (!isset($grouped[$cplId])) {
    //                 $grouped[$cplId] = 0;
    //             }
    //             $grouped[$cplId] += $item['bobot'];
    //             // Siapkan data untuk attach; Gunakan cpmk_id sebagai key
    //             $syncData[$item['cpmk_id']] = [
    //                 'cpl_id' => $cplId,
    //                 'bobot'  => $item['bobot']
    //             ];
    //         }

    //         // Ambil mapping CPL yang sudah terpasang pada mata kuliah (relasi pivot)
    //         $existingCplMappings = $mataKuliah->cpls()->get()->keyBy('cpl_id');

    //         foreach ($grouped as $cplId => $sumBobot) {
    //             if (!isset($existingCplMappings[$cplId])) {
    //                 throw new \Illuminate\Validation\ValidationException(
    //                     validator: validator([], []),
    //                     response: response()->json([
    //                         'message' => "Mapping CPL untuk cpl_id $cplId tidak ditemukan pada mata kuliah ini."
    //                     ], 422)
    //                 );
    //             }

    //             $cplBobot = $existingCplMappings[$cplId]->pivot->bobot;
    //             if ($sumBobot != $cplBobot) {
    //                 throw new \Illuminate\Validation\ValidationException(
    //                     validator: validator([], []),
    //                     response: response()->json([
    //                         'message' => "Total bobot CPMK untuk CPL $cplId tidak sama dengan bobot CPL yang ditetapkan."
    //                     ], 422)
    //                 );
    //             }
    //         }

    //         if ($mode === 'store') {
    //             // Pada mode store, tambahkan mapping baru tanpa menghapus yang sudah ada
    //             $mataKuliah->cpmks()->attach($syncData);
    //         } else { // mode update:
    //             // Lakukan update hanya untuk CPL yang terlibat pada payload
    //             // Ambil daftar cpl_id dari payload
    //             $cplIdsToUpdate = array_keys($grouped);
    //             // Hapus mapping lama pada pivot untuk CPL yang terkait.
    //             // Karena detach() dari relasi Eloquent menghapus berdasarkan cpmk_id,
    //             // untuk update spesifik berdasarkan cpl_id, kita gunakan query manual pada pivot.
    //             DB::table('cpmk_mata_kuliah')
    //                 ->where('mata_kuliah_id', $mataKuliahId)
    //                 ->whereIn('cpl_id', $cplIdsToUpdate)
    //                 ->delete();
    //             // Kemudian, attach data baru
    //             $mataKuliah->cpmks()->attach($syncData);
    //         }

    //         DB::commit();

    //         // Reload relasi menggunakan Eloquent
    //         $mataKuliah->load('cpmks');
    //         return [
    //             'data'    => $mataKuliah->cpmks,
    //             'message' => "Pemetaan CPMK berhasil " . ($mode === 'store' ? 'ditambahkan.' : 'diperbarui.')
    //         ];
    //     } catch (\Illuminate\Validation\ValidationException $ve) {
    //         DB::rollBack();
    //         $data = $ve->getResponse()->getData();
    //         return ['message' => $data->message];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return ['message' => 'Pemetaan CPMK gagal: ' . $e->getMessage()];
    //     }
    // }

    public function kelolaSubPenilaian(array $payload): array
    {
        return $this->subPenilaianService->kelolaSubPenilaian($payload);
    }
}
