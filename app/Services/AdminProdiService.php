<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AdminProdiService
{
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
                    // Jika terdapat parameter id, ambil data satu akun.
                    // Jika tidak, ambil keseluruhan akun dengan role kaprodi.
                    if (isset($data['id'])) {
                        $user = User::role('Kaprodi')->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Kaprodi berhasil diambil.'
                        ];
                    } else {
                        $users = User::role('Kaprodi')->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Kaprodi berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah akun Kaprodi baru.
                    DB::beginTransaction();
                    $user = User::create([
                        'name'     => $data['name'],
                        'email'    => $data['email'],
                        'password' => bcrypt($data['password']),
                    ]);
                    DB::commit();

                    // Assign role 'Kaprodi' menggunakan Spatie Permission.
                    $user->assignRole('Kaprodi');

                    return [
                        'data'    => $user,
                        'message' => 'Akun Kaprodi berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk update.'];
                    }

                    // Perbarui data akun Kaprodi.
                    DB::beginTransaction();
                    $user = User::findOrFail($data['id']);
                    $user->update([
                        'name'     => $data['name']     ?? $user->name,
                        'email'    => $data['email']    ?? $user->email,
                        'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
                    ]);

                    // Hanya perbarui password jika disediakan.
                    if (isset($data['password'])) {
                        $user->password = bcrypt($data['password']);
                    }

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Kaprodi berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus akun Kaprodi berdasarkan id.
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

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
                    // Jika terdapat parameter id, ambil data satu akun.
                    // Jika tidak, ambil keseluruhan akun dengan role dosen.
                    if (isset($data['id'])) {
                        $user = User::role('Dosen')->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Dosen berhasil diambil.'
                        ];
                    } else {
                        $users = User::role('Dosen')->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Dosen berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah akun Dosen baru.
                    DB::beginTransaction();
                    $user = User::create([
                        'name'     => $data['name'],
                        'email'    => $data['email'],
                        'password' => bcrypt($data['password']),
                    ]);
                    DB::commit();

                    // Assign role 'Dosen' menggunakan Spatie Permission.
                    $user->assignRole('Dosen');

                    return [
                        'data'    => $user,
                        'message' => 'Akun Dosen berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk update.'];
                    }

                    // Perbarui data akun Dosen.
                    DB::beginTransaction();
                    $user = User::findOrFail($data['id']);
                    $user->update([
                        'name'     => $data['name']     ?? $user->name,
                        'email'    => $data['email']    ?? $user->email,
                        'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
                    ]);

                    // Hanya perbarui password jika disediakan.
                    if (isset($data['password'])) {
                        $user->password = bcrypt($data['password']);
                    }

                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Dosen berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus akun Dosen berdasarkan id.
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk dihapus.'];
                    }

                    DB::beginTransaction();

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
                            ->select('mahasiswa_id', 'npm', 'name', 'email', 'prodi_id')
                            ->findOrFail($data['mahasiswa_id']);
                        return [
                            'data'    => $mahasiswa,
                            'message' => 'Data mahasiswa berhasil diambil.'
                        ];
                    } else {
                        $mahasiswas = Mahasiswa::with(['prodi' => function ($query) {
                            $query->select('prodi_id', 'kode_prodi', 'nama_prodi');
                        }])
                            ->select('mahasiswa_id', 'npm', 'name', 'email', 'prodi_id')
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
                        'email'    => $data['email'],
                        'prodi_id' => $data['prodi_id'],
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
                        'email'    => $data['email']    ?? $mahasiswa->email,
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
                        'prodi_id' => $data['prodi_id'],
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

                    // Ambil prodi_id dari request atau gunakan yang sudah ada
                    $prodiId = $data['prodi_id'] ?? $mataKuliah->prodi->prodi_id;

                    // Pastikan prodi yang dikirimkan ada dalam database sebelum update
                    if (!Prodi::where('prodi_id', $prodiId)->exists()) {
                        return ['message' => 'Prodi yang diberikan tidak valid atau tidak ditemukan.'];
                    }

                    $mataKuliah->update([
                        'kode_mata_kuliah'     => $data['kode_mata_kuliah']     ?? $mataKuliah->kode_mata_kuliah,
                        'nama_mata_kuliah'     => $data['nama_mata_kuliah']     ?? $mataKuliah->nama_mata_kuliah,
                        'prodi_id' => $prodiId
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
}
