<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AkunAdminUniversitasTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $role;
    protected $permission;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat role "Admin Universitas" dan permission
        $this->role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola akun admin universitas', 'guard_name' => 'web']);

        // Buat user untuk request dan assign role serta berikan permission
        $this->user = User::factory()->create();
        $this->user->assignRole($this->role);
        $this->role->givePermissionTo($this->permission);
    }

    public function test_view_all_akun_admin_universitas(): void
    {
        // Buat beberapa akun Admin Universitas di database.
        $admin1 = User::factory()->create([
            'name'  => 'Admin Universitas 1',
            'email' => 'admin1@example.com'
        ]);
        $admin1->assignRole($this->role);

        $admin2 = User::factory()->create([
            'name'  => 'Admin Universitas 2',
            'email' => 'admin2@example.com'
        ]);
        $admin2->assignRole($this->role);

        // Siapkan payload request
        $payload = [
            'action' => 'view',
        ];

        // Kirim request POST dengan menggunakan user yang sudah disiapkan di setUp()
        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-universitas', $payload);

        // Pastikan respon memiliki status 200 dan pesan yang sesuai
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data akun Admin Universitas berhasil diambil.'
            ]);

        // Opsional: pastikan data yang dikembalikan berupa array dan setidaknya terdapat 2 data
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_view_detail_akun_admin_universitas_berdasarkan_id(): void
    {
        // Buat akun admin yang akan diambil detailnya
        $admin = User::factory()->create([
            'name'  => 'Admin Universitas Detail',
            'email' => 'detail@example.com',
        ]);
        $admin->assignRole($this->role);

        $payload = [
            'action' => 'view',
            'id'     => $admin->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-universitas', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data akun Admin Universitas berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($admin->id, $data['id']);
        $this->assertEquals($admin->email, $data['email']);
    }

    public function test_store_akun_admin_universitas_berhasil(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => 'Admin Universitas 1',
            'email'    => 'admin1@example.com',
            'password' => 'password123',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-universitas', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Akun Admin Universitas berhasil dibuat.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin1@example.com'
        ]);
    }

    public function test_store_akun_admin_universitas_validasi_gagal(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => '', // Nama kosong harusnya gagal
            'email'    => 'invalid-email', // Format email salah
            'password' => 'short', // Password terlalu pendek
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-universitas', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_update_akun_admin_universitas_berhasil(): void
    {
        // Buat akun admin awal
        $admin = User::factory()->create([
            'name'  => 'Admin Old Name',
            'email' => 'adminold@example.com',
        ]);
        $admin->assignRole($this->role);

        $updatePayload = [
            'action'   => 'update',
            'id'       => $admin->id,
            'name'     => 'Admin New Name',
            'email'    => 'adminnew@example.com',
            'password' => 'newpassword123',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-universitas', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Admin Universitas berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('users', [
            'id'    => $admin->id,
            'name'  => 'Admin New Name',
            'email' => 'adminnew@example.com',
        ]);
    }

    public function test_update_akun_admin_universitas_validasi_gagal(): void
    {
        // Buat akun admin untuk diuji validasinya
        $admin = User::factory()->create([
            'name'  => 'Admin Existing',
            'email' => 'existingadmin@example.com',
        ]);
        $admin->assignRole($this->role);

        $invalidPayload = [
            'action'   => 'update',
            'id'       => $admin->id,
            'name'     => '', // Nama kosong
            'email'    => 'not-an-email', // Format email salah
            'password' => 'short', // Password terlalu pendek
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-universitas', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_delete_akun_admin_universitas_berhasil(): void
    {
        // Buat akun admin yang akan dihapus
        $admin = User::factory()->create([
            'name'  => 'Test Admin',
            'email' => 'testadmin@example.com',
        ]);
        $admin->assignRole($this->role);

        $payload = [
            'action' => 'delete',
            'id'     => $admin->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-universitas', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Admin Universitas berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('users', [
            'id'    => $admin->id,
            'email' => $admin->email,
        ]);
    }
}
