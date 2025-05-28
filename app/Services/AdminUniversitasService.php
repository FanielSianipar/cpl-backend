<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class AdminUniversitasService
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
    public function kelolaAkunAdminUniversitas(array $data)
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // Jika terdapat parameter id, ambil data satu akun.
                    // Jika tidak, ambil keseluruhan akun dengan role admin universitas.
                    if (isset($data['id'])) {
                        $user = User::role('Admin Universitas')->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Admin Universitas berhasil diambil.'
                        ];
                    } else {
                        $users = User::role('Admin Universitas')
                            ->where('id', '!=', auth()->id())
                            ->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Admin Universitas berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah akun Admin Universitas baru.
                    DB::beginTransaction();

                    $user = new User();
                    $user->name     = $data['name'];
                    $user->email    = $data['email'];
                    $user->password = Hash::make($data['password']);
                    $user->save();
                    DB::commit();

                    // Assign role 'admin universitas' menggunakan Spatie Permission.
                    $user->assignRole('Admin Universitas');

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Universitas berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return [
                            'message' => 'ID akun tidak ditemukan untuk update.'
                        ];
                    }

                    // Perbarui data akun Admin Universitas.
                    DB::beginTransaction();

                    $user = User::role('Admin Universitas')->findOrFail($data['id']);
                    $user->name  = isset($data['name']) ? $data['name'] : $user->name;
                    $user->email = isset($data['email']) ? $data['email'] : $user->email;

                    // Hanya perbarui password jika disediakan.
                    if (isset($data['password']) && !empty($data['password'])) {
                        $user->password = Hash::make($data['password']);
                    }

                    $user->save();
                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Universitas berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus akun Admin Universitas berdasarkan id.
                    if (!isset($data['id'])) {
                        return ['message' => 'ID akun tidak ditemukan untuk delete.'];
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
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function kelolaAkunAdminProdi(array $data)
    {
        $action = $data['action'] ?? null;

        try {
            switch ($action) {
                case 'view':
                    // Jika terdapat parameter id, ambil data satu akun.
                    // Jika tidak, ambil keseluruhan akun dengan role admin prodi.
                    if (isset($data['id'])) {
                        $user = User::role('Admin Prodi')->findOrFail($data['id']);
                        return [
                            'data'    => $user,
                            'message' => 'Data akun Admin Prodi berhasil diambil.'
                        ];
                    } else {
                        $users = User::role('Admin Prodi')
                            ->where('id', '!=', auth()->id())
                            ->get();
                        return [
                            'data'    => $users,
                            'message' => 'Semua data akun Admin Prodi berhasil diambil.'
                        ];
                    }
                    break;

                case 'store':
                    // Tambah akun Admin Prodi baru.
                    DB::beginTransaction();

                    $user = new User();
                    $user->name     = $data['name'];
                    $user->email    = $data['email'];
                    $user->password = Hash::make($data['password']);
                    $user->save();
                    DB::commit();

                    // Assign role 'admin prodi' menggunakan Spatie Permission.
                    $user->assignRole('Admin Prodi');

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Prodi berhasil dibuat.'
                    ];
                    break;

                case 'update':
                    if (!isset($data['id'])) {
                        return [
                            'message' => 'ID akun tidak ditemukan untuk update.'
                        ];
                    }

                    // Perbarui data akun Admin Prodi.
                    DB::beginTransaction();

                    $user = User::role('Admin Prodi')->findOrFail($data['id']);
                    $user->name  = isset($data['name']) ? $data['name'] : $user->name;
                    $user->email = isset($data['email']) ? $data['email'] : $user->email;

                    // Hanya perbarui password jika disediakan.
                    if (isset($data['password']) && !empty($data['password'])) {
                        $user->password = Hash::make($data['password']);
                    }

                    $user->save();
                    DB::commit();

                    return [
                        'data'    => $user,
                        'message' => 'Akun Admin Prodi berhasil diperbarui.'
                    ];
                    break;

                case 'delete':
                    // Hapus akun Admin Prodi berdasarkan id.
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
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
