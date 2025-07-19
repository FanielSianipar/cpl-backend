<?php

namespace App\Services;

use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

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
                        $user = User::role('Admin Universitas')->findOrFail($data['id']);
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
                            ->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Admin Prodi berhasil diambil.'
                        ];
                    } else {
                        $users = User::role('Admin Prodi')
                            ->where('id', '!=', auth()->id())
                            ->with('prodi')
                            ->select('id', 'name', 'email', 'prodi_id')
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
}
