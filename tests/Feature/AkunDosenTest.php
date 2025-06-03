<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AkunDosenTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Test mengambil seluruh akun Admin Prodi.
     */
    public function test_view_all_akun_dosen(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun dosen');

        $payload = [
            'action' => 'view'
        ];
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-dosen', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data akun Dosen berhasil diambil.'
            ]);
    }

    /**
     * Test mengambil detail satu akun Admin Prodi berdasarkan id.
     */
    public function test_view_detail_akun_dosen(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun dosen');

        // Buat akun Dosen yang akan diambil datanya.
        $dosen = User::factory()->create([
            'name' => 'Dosen Test',
            'email' => 'dosen@example.com'
        ]);
        $dosen->assignRole('Dosen');

        $payload = [
            'action' => 'view',
            'id'     => $dosen->id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data akun Dosen berhasil diambil.'
            ]);

        // Pastikan data yang dikembalikan benar (cukup cek email dan id saja).
        $data = $response->json('data');
        $this->assertEquals($dosen->id, $data['id']);
        $this->assertEquals($dosen->email, $data['email']);
    }

    public function test_store_akun_dosen(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun dosen');

        $payload = [
            'action'   => 'store',
            'name'     => 'Dosen Baru',
            'email'    => 'dosen@example.com',
            'password' => 'password123'
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Akun Dosen berhasil dibuat.'
            ]);

        // Pastikan data benar-benar masuk ke database
        $this->assertDatabaseHas('users', [
            'email' => 'dosen@example.com'
        ]);
    }

    public function test_store_akun_dosen_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun dosen');

        $payload = [
            'action'   => 'store',
            'name' => '', // Kosong, harusnya invalid
            'email' => 'invalid-email', // Format salah
            'password' => 'short' // Password kurang dari 8 karakter
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_update_akun_dosen_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun dosen');

        // Buat akun dosen yang akan diupdate
        $roleDosen = Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);
        $dosen = User::factory()->create([
            'name'  => 'Dosen Old Name',
            'email' => 'dosenold@example.com',
        ]);
        $dosen->assignRole($roleDosen);

        $payload = [
            'action'                => 'update',
            'id'                    => $dosen->id,
            'name'                  => 'Dosen New Name',
            'email'                 => 'dosennew@example.com',
            'password'              => 'newpassword123'
        ];

        // Lakukan request update dengan method POST sesuai dengan route Anda
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-dosen', $payload);

        // Pastikan response berhasil dengan status 200 dan pesan yang sesuai
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Dosen berhasil diperbarui.'
            ]);

        // Pastikan data pada database sudah terupdate
        $this->assertDatabaseHas('users', [
            'id'    => $dosen->id,
            'name'  => 'Dosen New Name',
            'email' => 'dosennew@example.com',
        ]);
    }

    public function test_update_akun_dosen_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun dosen');

        // Buat akun Dosen yang akan diupdate
        $roleDosen = Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);
        $dosen = User::factory()->create([
            'name'  => 'Dosen To Update',
            'email' => 'dosen@example.com',
        ]);
        $dosen->assignRole($roleDosen);

        $payload = [
            'action'                => 'update',
            'id'                    => $dosen->id,
            'name'                  => '', // Kosong, harusnya invalid
            'email'                 => 'invalid-email', // Format salah
            'password'              => 'short' // Password kurang dari 8 karakter
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_delete_akun_dosen_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun dosen');

        // Buat akun Dosen yang akan dihapus
        $roleDosen = Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);
        $dosen = User::factory()->create([
            'name'  => 'Dosen To Update',
            'email' => 'dosen@example.com',
        ]);
        $dosen->assignRole($roleDosen);

        $payload = [
            'action'                => 'delete',
            'id'                    => $dosen->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Dosen berhasil dihapus.'
            ]);

        // Pastikan data sudah terhapus dari database
        $this->assertDatabaseMissing('users', [
            'id'    => $dosen->id,
            'email' => $dosen->email
        ]);
    }
}
