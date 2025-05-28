<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AkunAdminUniversitasTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test mengambil seluruh akun Admin Universitas.
     */
    public function test_view_all_akun_admin_universitas(): void
    {
        // Buat role "Admin Universitas"
        Role::firstOrCreate(['name' => 'Admin Universitas']);
        Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas']);

        // Buat user dengan permission dan role
        $user = User::factory()->create();
        $user->assignRole('Admin Universitas');
        $role = Role::where('name', 'Admin Universitas')->first();
        $role->givePermissionTo('Mengelola akun admin universitas');

        // Buat beberapa akun Admin Universitas untuk kebutuhan testing.
        $admin1 = User::factory()->create([
            'name'  => 'Admin Universitas 1',
            'email' => 'admin1@example.com'
        ]);
        $admin1->assignRole('Admin Universitas');

        $admin2 = User::factory()->create([
            'name'  => 'Admin Universitas 2',
            'email' => 'admin2@example.com'
        ]);
        $admin2->assignRole('Admin Universitas');

        // Lakukan GET request ke endpoint view.
        $response = $this->actingAs($user)
            ->getJson('/api/kelola-akun-admin-universitas?action=view');

        // Pastikan response memiliki status 200 dan pesan yang sesuai.
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data akun Admin Universitas berhasil diambil.'
            ]);
    }

    /**
     * Test mengambil detail satu akun Admin Universitas berdasarkan id.
     */
    public function test_view_detail_akun_admin_universitas_berdasarkan_id(): void
    {
        // Buat role "Admin Universitas"
        Role::firstOrCreate(['name' => 'Admin Universitas']);
        Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas']);

        // Buat user dengan permission dan role
        $user = User::factory()->create();
        $user->assignRole('Admin Universitas');
        $role = Role::where('name', 'Admin Universitas')->first();
        $role->givePermissionTo('Mengelola akun admin universitas');

        // Buat akun Admin Universitas yang akan diambil datanya.
        $admin = User::factory()->create([
            'name'  => 'Admin Universitas Detail',
            'email' => 'detail@example.com'
        ]);
        $admin->assignRole('Admin Universitas');

        // Lakukan GET request dengan parameter "id" untuk mengambil detail akun.
        $response = $this->actingAs($user)
            ->getJson('/api/kelola-akun-admin-universitas?action=view&id=' . $admin->id);

        // Pastikan response memiliki status 200 dan pesan sesuai.
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data akun Admin Universitas berhasil diambil.',
            ]);

        // Pastikan data yang dikembalikan benar (cukup cek email dan id saja).
        $data = $response->json('data');
        $this->assertEquals($admin->id, $data['id']);
        $this->assertEquals($admin->email, $data['email']);
    }

    public function test_store_akun_admin_universitas_berhasil(): void
    {
        // Buat role "Admin Universitas"
        Role::firstOrCreate(['name' => 'Admin Universitas']);
        Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas']);

        // Buat user dengan permission dan role
        $user = User::factory()->create();
        $user->assignRole('Admin Universitas');
        $role = Role::where('name', 'Admin Universitas')->first();
        $role->givePermissionTo('Mengelola akun admin universitas');

        // Kirim request sebagai user yang sudah memiliki role dan permission
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-admin-universitas', [
            'action' => 'store',
            'name' => 'Admin Universitas 1',
            'email' => 'admin1@example.com',
            'password' => 'password123'
        ]);

        // Pastikan response berhasil dan statusnya 201 Created
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Akun Admin Universitas berhasil dibuat.'
            ]);

        // Pastikan data benar-benar masuk ke database
        $this->assertDatabaseHas('users', [
            'email' => 'admin1@example.com'
        ]);
    }

    public function test_store_akun_admin_universitas_validasi_gagal(): void
    {
        // Buat role "Admin Universitas"
        Role::firstOrCreate(['name' => 'Admin Universitas']);
        Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas']);

        // Buat user dengan permission dan role
        $user = User::factory()->create();
        $user->assignRole('Admin Universitas');
        $role = Role::where('name', 'Admin Universitas')->first();
        $role->givePermissionTo('Mengelola akun admin universitas');

        // Kirim request dengan data yang tidak valid
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-admin-universitas', [
            'action' => 'store',
            'name' => '', // Kosong, harusnya invalid
            'email' => 'invalid-email', // Format salah
            'password' => 'short' // Password kurang dari 8 karakter
        ]);

        // Pastikan response error dengan status 422 (Unprocessable Entity)
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test update akun admin universitas berhasil.
     */
    public function test_update_akun_admin_universitas_berhasil(): void
    {
        // Buat role dan permission menggunakan firstOrCreate agar tidak terjadi duplicate
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas', 'guard_name' => 'web']);

        // Buat user yang akan melakukan update (user pelaku request)
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun admin universitas');

        // Buat akun admin yang akan diupdate
        $admin = User::factory()->create([
            'name'  => 'Admin Old Name',
            'email' => 'adminold@example.com',
        ]);
        $admin->assignRole($role);

        // Data update yang valid
        $updateData = [
            'action'                => 'update',
            'id'                    => $admin->id,
            'name'                  => 'Admin New Name',
            'email'                 => 'adminnew@example.com',
            'password'              => 'newpassword123'
        ];

        // Lakukan request update dengan method POST sesuai dengan route Anda
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-admin-universitas', $updateData);

        // Pastikan response berhasil dengan status 200 dan pesan yang sesuai
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Admin Universitas berhasil diperbarui.'
            ]);

        // Pastikan data pada database sudah terupdate
        $this->assertDatabaseHas('users', [
            'id'    => $admin->id,
            'name'  => 'Admin New Name',
            'email' => 'adminnew@example.com',
        ]);
    }

    /**
     * Test update akun admin universitas gagal karena validasi.
     */
    public function test_update_akun_admin_universitas_validasi_gagal(): void
    {
        // Buat role dan permission
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas', 'guard_name' => 'web']);

        // Buat user yang akan melakukan request update
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun admin universitas');

        // Buat akun admin yang akan diupdate
        $admin = User::factory()->create([
            'name'  => 'Admin Existing',
            'email' => 'existingadmin@example.com',
        ]);
        $admin->assignRole($role);

        // Kirim data update yang tidak valid: nama kosong, email format salah, password terlalu pendek
        $updateData = [
            'action' => 'update',
            'id'     => $admin->id,
            'name'   => '',
            'email'  => 'not-an-email',
            'password' => 'short',
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-admin-universitas', $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }


    public function test_delete_akun_admin_universitas_berhasil(): void
    {
        // Buat role dan permission (gunakan firstOrCreate untuk menghindari error duplicate)
        $role = Role::firstOrCreate(['name' => 'Admin Universitas']);
        Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas']);

        // Buat user yang akan melakukan request
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun admin universitas');

        // Buat akun admin yang akan dihapus
        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'testadmin@example.com'
        ]);
        $admin->assignRole($role);

        // Jalankan request delete dengan method POST dan parameter action 'delete'
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-admin-universitas', [
            'action' => 'delete',
            'id'     => $admin->id,
        ]);

        // Pastikan response status 200 dan pesan sesuai
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Admin Universitas berhasil dihapus.'
            ]);

        // Pastikan data user yang dihapus tidak ada di database
        $this->assertDatabaseMissing('users', [
            'id'    => $admin->id,
            'email' => $admin->email,
        ]);
    }
}
