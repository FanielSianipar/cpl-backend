<?php

namespace Tests\Feature;

use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AkunKaprodiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminProdiRole;
    protected $kaprodiRole;
    protected $permission;
    protected $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat role Admin Prodi sebagai pengguna yang berhak mengelola Kaprodi
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);

        // Buat role Kaprodi yang akan digunakan oleh akun yang dikelola
        $this->kaprodiRole = Role::firstOrCreate(['name' => 'Kaprodi', 'guard_name' => 'web']);

        // Buat permission untuk mengelola akun Kaprodi
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create();
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);

        // Buat satu record Prodi untuk keperluan testing
        $this->prodi = Prodi::factory()->create(['nama_prodi' => 'Teknik Informatika']);
    }

    /**
     * Test mengambil seluruh akun Kaprodi.
     */
    public function test_view_all_akun_kaprodi(): void
    {
        $kaprodi1 = User::factory()->create([
            'name'     => 'Kaprodi 1',
            'email'    => 'kaprodi1@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $kaprodi1->assignRole($this->kaprodiRole);

        $kaprodi2 = User::factory()->create([
            'name'     => 'Kaprodi 2',
            'email'    => 'kaprodi2@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $kaprodi2->assignRole($this->kaprodiRole);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data akun Kaprodi berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));

        foreach ($data as $userData) {
            $this->assertArrayHasKey('prodi_id', $userData);
        }
    }

    /**
     * Test mengambil detail satu akun Kaprodi berdasarkan ID.
     */
    public function test_view_detail_akun_kaprodi(): void
    {
        $kaprodi = User::factory()->create([
            'name'     => 'Kaprodi Detail',
            'email'    => 'kaprodi_detail@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $kaprodi->assignRole($this->kaprodiRole);

        $payload = ['action' => 'view', 'id' => $kaprodi->id];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data akun Kaprodi berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($kaprodi->id, $data['id']);
        $this->assertEquals($kaprodi->email, $data['email']);
        $this->assertEquals($this->prodi->prodi_id, $data['prodi_id']);
    }

    /**
     * Test pembuatan akun Kaprodi berhasil.
     */
    public function test_store_akun_kaprodi(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => 'Kaprodi Baru',
            'email'    => 'kaprodi_baru@example.com',
            'password' => 'password123',
            'prodi_id' => $this->prodi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Akun Kaprodi berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('users', [
            'email'    => 'kaprodi_baru@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $createdUser = User::where('email', 'kaprodi_baru@example.com')->first();
        $this->assertTrue($createdUser->hasRole('Kaprodi'));
    }

    /**
     * Test validasi gagal saat pembuatan akun Kaprodi.
     */
    public function test_store_akun_kaprodi_validasi_gagal(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => '',
            'email'    => 'not-an-email',
            'password' => 'short',
            'prodi_id' => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'prodi_id']);
    }

    /**
     * Test update akun Kaprodi berhasil.
     */
    public function test_update_akun_kaprodi_berhasil(): void
    {
        // Buat akun Kaprodi awal
        $kaprodi = User::factory()->create([
            'name'     => 'Kaprodi Lama',
            'email'    => 'kaprodi_lama@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $kaprodi->assignRole($this->kaprodiRole);

        // Jika ingin update ke prodi yang berbeda, buat terlebih dahulu record prodi baru
        $newProdi = Prodi::factory()->create(['nama_prodi' => 'Sistem Informasi']);

        $updatePayload = [
            'action'   => 'update',
            'id'       => $kaprodi->id,
            'name'     => 'Kaprodi Baru',
            'email'    => 'kaprodi_baru_updated@example.com',
            'password' => 'newpassword123',
            'prodi_id' => $newProdi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-kaprodi', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Kaprodi berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('users', [
            'id'       => $kaprodi->id,
            'name'     => 'Kaprodi Baru',
            'email'    => 'kaprodi_baru_updated@example.com',
            'prodi_id' => $newProdi->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat update akun Kaprodi.
     */
    public function test_update_akun_kaprodi_validasi_gagal(): void
    {
        // Buat akun Kaprodi untuk diuji validasinya
        $kaprodi = User::factory()->create([
            'name'     => 'Kaprodi Existing',
            'email'    => 'kaprodi_existing@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $kaprodi->assignRole($this->kaprodiRole);

        $invalidPayload = [
            'action'   => 'update',
            'id'       => $kaprodi->id,
            'name'     => '',             // Nama kosong -> invalid
            'email'    => 'not-an-email', // Format email salah
            'password' => 'short',        // Password terlalu pendek
            'prodi_id' => '',             // Prodi_id tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-kaprodi', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'prodi_id']);
    }

    /**
     * Test penghapusan akun Kaprodi berhasil.
     */
    public function test_delete_akun_kaprodi_berhasil(): void
    {
        $kaprodi = User::factory()->create([
            'name'     => 'Kaprodi Delete',
            'email'    => 'kaprodi_delete@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $kaprodi->assignRole($this->kaprodiRole);

        $payload = ['action' => 'delete', 'id' => $kaprodi->id];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Kaprodi berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('users', [
            'id'    => $kaprodi->id,
            'email' => $kaprodi->email,
        ]);
    }
}
