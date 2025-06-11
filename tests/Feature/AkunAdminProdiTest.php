<?php

namespace Tests\Feature;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AkunAdminProdiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminUniversitasRole;
    protected $adminProdiRole;
    protected $permission;
    protected $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat role "Admin Universitas" untuk user yang melakukan request
        $this->adminUniversitasRole = Role::firstOrCreate([
            'name'       => 'Admin Universitas',
            'guard_name' => 'web'
        ]);

        // Buat role "Admin Prodi" untuk akun yang dikelola
        $this->adminProdiRole = Role::firstOrCreate([
            'name'       => 'Admin Prodi',
            'guard_name' => 'web'
        ]);

        // Buat permission untuk mengelola akun Admin Prodi
        $this->permission = Permission::firstOrCreate([
            'name'       => 'Mengelola akun admin prodi',
            'guard_name' => 'web'
        ]);

        // Buat user acting (Admin Universitas) dan assign role serta permission
        $this->user = User::factory()->create();
        $this->user->assignRole($this->adminUniversitasRole);
        $this->adminUniversitasRole->givePermissionTo($this->permission);

        // Buat satu record Prodi untuk keperluan testing
        $this->prodi = Prodi::factory()->create(['nama_prodi' => 'Teknik Informatika']);
    }

    /**
     * Test mengambil seluruh akun Admin Prodi.
     */
    public function test_view_all_akun_admin_prodi(): void
    {
        // Buat beberapa akun Admin Prodi
        $admin1 = User::factory()->create([
            'name'     => 'Admin Prodi 1',
            'email'    => 'adminprodi1@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $admin1->assignRole($this->adminProdiRole);

        $admin2 = User::factory()->create([
            'name'     => 'Admin Prodi 2',
            'email'    => 'adminprodi2@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $admin2->assignRole($this->adminProdiRole);

        $payload = [
            'action' => 'view',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-prodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data akun Admin Prodi berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));

        // Pastikan setiap data memiliki field prodi_id
        foreach ($data as $userData) {
            $this->assertArrayHasKey('prodi_id', $userData);
        }
    }

    /**
     * Test mengambil detail satu akun Admin Prodi berdasarkan ID.
     */
    public function test_view_detail_akun_admin_prodi_berdasarkan_id(): void
    {
        $admin = User::factory()->create([
            'name'     => 'Admin Prodi Detail',
            'email'    => 'detailadminprodi@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $admin->assignRole($this->adminProdiRole);

        $payload = [
            'action' => 'view',
            'id'     => $admin->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-prodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data akun Admin Prodi berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($admin->id, $data['id']);
        $this->assertEquals($admin->email, $data['email']);
        $this->assertEquals($this->prodi->prodi_id, $data['prodi_id']);
    }

    /**
     * Test pembuatan akun Admin Prodi berhasil.
     */
    public function test_store_akun_admin_prodi_berhasil(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => 'Admin Prodi Baru',
            'email'    => 'adminprodi_baru@example.com',
            'password' => 'password123',
            'prodi_id' => $this->prodi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-prodi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Akun Admin Prodi berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('users', [
            'email'    => 'adminprodi_baru@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $createdUser = User::where('email', 'adminprodi_baru@example.com')->first();
        $this->assertTrue($createdUser->hasRole('Admin Prodi'));
    }

    /**
     * Test validasi gagal saat pembuatan akun Admin Prodi.
     */
    public function test_store_akun_admin_prodi_validasi_gagal(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => '',              // Nama kosong -> invalid
            'email'    => 'not-an-email',  // Format email salah
            'password' => 'short',         // Password terlalu pendek
            'prodi_id' => '',              // Prodi_id kosong -> invalid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-prodi', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'prodi_id']);
    }

    /**
     * Test update akun Admin Prodi berhasil.
     */
    public function test_update_akun_admin_prodi_berhasil(): void
    {
        // Buat akun Admin Prodi awal
        $admin = User::factory()->create([
            'name'     => 'Admin Prodi Lama',
            'email'    => 'adminprodi_lama@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $admin->assignRole($this->adminProdiRole);

        // Jika ingin update ke prodi yang berbeda, buat terlebih dahulu record prodi baru
        $newProdi = Prodi::factory()->create([
            'nama_prodi' => 'Sistem Informasi'
        ]);

        $updatePayload = [
            'action'   => 'update',
            'id'       => $admin->id,
            'name'     => 'Admin Prodi Baru',
            'email'    => 'adminprodi_baru_updated@example.com',
            'password' => 'newpassword123',
            'prodi_id' => $newProdi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-prodi', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Admin Prodi berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('users', [
            'id'       => $admin->id,
            'name'     => 'Admin Prodi Baru',
            'email'    => 'adminprodi_baru_updated@example.com',
            'prodi_id' => $newProdi->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat update akun Admin Prodi.
     */
    public function test_update_akun_admin_prodi_validasi_gagal(): void
    {
        $admin = User::factory()->create([
            'name'     => 'Admin Prodi Existing',
            'email'    => 'existing_adminprodi@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $admin->assignRole($this->adminProdiRole);

        $invalidPayload = [
            'action'   => 'update',
            'id'       => $admin->id,
            'name'     => '',             // Nama kosong -> invalid
            'email'    => 'not-an-email', // Format email salah
            'password' => 'short',        // Password terlalu pendek
            'prodi_id' => '',             // Prodi_id tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-prodi', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'prodi_id']);
    }

    /**
     * Test penghapusan akun Admin Prodi berhasil.
     */
    public function test_delete_akun_admin_prodi_berhasil(): void
    {
        $admin = User::factory()->create([
            'name'     => 'Admin Prodi Delete',
            'email'    => 'delete_adminprodi@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $admin->assignRole($this->adminProdiRole);

        $payload = [
            'action' => 'delete',
            'id'     => $admin->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-admin-prodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Admin Prodi berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('users', [
            'id'    => $admin->id,
            'email' => $admin->email,
        ]);
    }
}
