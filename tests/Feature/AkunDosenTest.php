<?php

namespace Tests\Feature;

use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AkunDosenTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminProdiRole;
    protected $dosenRole;
    protected $permission;
    protected $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat role Admin Prodi sebagai pengguna yang berhak mengelola Dosen
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);

        // Buat role Dosen yang akan digunakan oleh akun yang dikelola
        $this->dosenRole = Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);

        // Buat permission untuk mengelola akun Dosen
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola akun dosen', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create();
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);

        // Buat satu record Prodi untuk keperluan testing
        $this->prodi = Prodi::factory()->create(['nama_prodi' => 'Teknik Informatika']);
    }

    /**
     * Test mengambil seluruh akun Dosen.
     */
    public function test_view_all_akun_dosen(): void
    {
        $dosen1 = User::factory()->create([
            'name'     => 'Dosen 1',
            'email'    => 'dosen1@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $dosen1->assignRole($this->dosenRole);

        $dosen2 = User::factory()->create([
            'name'     => 'Dosen 2',
            'email'    => 'dosen2@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $dosen2->assignRole($this->dosenRole);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data akun Dosen berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));

        foreach ($data as $userData) {
            $this->assertArrayHasKey('prodi_id', $userData);
        }
    }

    /**
     * Test mengambil detail satu akun Dosen berdasarkan ID.
     */
    public function test_view_detail_akun_dosen(): void
    {
        $dosen = User::factory()->create([
            'name'     => 'Dosen Detail',
            'email'    => 'dosen_detail@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $dosen->assignRole($this->dosenRole);

        $payload = ['action' => 'view', 'id' => $dosen->id];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data akun Dosen berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($dosen->id, $data['id']);
        $this->assertEquals($dosen->email, $data['email']);
        $this->assertEquals($this->prodi->prodi_id, $data['prodi_id']);
    }

    /**
     * Test pembuatan akun Dosen berhasil.
     */
    public function test_store_akun_dosen(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => 'Dosen Baru',
            'email'    => 'dosen_baru@example.com',
            'password' => 'password123',
            'prodi_id' => $this->prodi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Akun Dosen berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('users', [
            'email'    => 'dosen_baru@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $createdUser = User::where('email', 'dosen_baru@example.com')->first();
        $this->assertTrue($createdUser->hasRole('Dosen'));
    }

    /**
     * Test validasi gagal saat pembuatan akun Dosen.
     */
    public function test_store_akun_dosen_validasi_gagal(): void
    {
        $payload = [
            'action'   => 'store',
            'name'     => '',
            'email'    => 'not-an-email',
            'password' => 'short',
            'prodi_id' => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'prodi_id']);
    }

    /**
     * Test update akun Dosen berhasil.
     */
    public function test_update_akun_dosen_berhasil(): void
    {
        // Buat akun Dosen awal
        $dosen = User::factory()->create([
            'name'     => 'Dosen Lama',
            'email'    => 'dosen_lama@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $dosen->assignRole($this->dosenRole);

        // Jika ingin update ke prodi yang berbeda, buat terlebih dahulu record prodi baru
        $newProdi = Prodi::factory()->create(['nama_prodi' => 'Sistem Informasi']);

        $updatePayload = [
            'action'   => 'update',
            'id'       => $dosen->id,
            'name'     => 'Dosen Baru',
            'email'    => 'dosen_baru_updated@example.com',
            'password' => 'newpassword123',
            'prodi_id' => $newProdi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-dosen', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Dosen berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('users', [
            'id'       => $dosen->id,
            'name'     => 'Dosen Baru',
            'email'    => 'dosen_baru_updated@example.com',
            'prodi_id' => $newProdi->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat update akun Dosen.
     */
    public function test_update_akun_dosen_validasi_gagal(): void
    {
        // Buat akun Dosen untuk diuji validasinya
        $dosen = User::factory()->create([
            'name'     => 'Dosen Existing',
            'email'    => 'dosen_existing@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $dosen->assignRole($this->dosenRole);

        $invalidPayload = [
            'action'   => 'update',
            'id'       => $dosen->id,
            'name'     => '',             // Nama kosong -> invalid
            'email'    => 'not-an-email', // Format email salah
            'password' => 'short',        // Password terlalu pendek
            'prodi_id' => '',             // Prodi_id tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-dosen', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'prodi_id']);
    }

    /**
     * Test penghapusan akun Dosen berhasil.
     */
    public function test_delete_akun_dosen_berhasil(): void
    {
        $dosen = User::factory()->create([
            'name'     => 'Dosen Delete',
            'email'    => 'dosen_delete@example.com',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $dosen->assignRole($this->dosenRole);

        $payload = ['action' => 'delete', 'id' => $dosen->id];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-akun-dosen', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Dosen berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('users', [
            'id'    => $dosen->id,
            'email' => $dosen->email,
        ]);
    }
}
