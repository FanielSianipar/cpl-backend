<?php

namespace Tests\Feature;

use App\Models\CPL;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataCplTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminProdiRole;
    protected $permission;
    protected $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada data Fakultas dan Prodi untuk operasi CPL
        $fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK100',
            'nama_fakultas' => 'Pertambangan',
        ]);

        $this->prodi = Prodi::factory()->create([
            'kode_prodi' => 'PRD100',
            'nama_prodi' => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        // Buat role Admin Prodi dengan izin mengelola CPL
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create();
        $this->user->prodi_id = $this->prodi->prodi_id;
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);
    }

    /**
     * Test mengambil seluruh data CPL.
     */
    public function test_view_all_data_cpl(): void
    {
        Cpl::factory()->create([
            'kode_cpl' => 'CPL444',
            'nama_cpl' => 'Nama1 CPL',
            'deskripsi' => 'Deskripsi1 CPL',
            'prodi_id' => $this->user->prodi_id,
        ]);

        Cpl::factory()->create([
            'kode_cpl' => 'CPL555',
            'nama_cpl' => 'Nama2 CPL',
            'deskripsi' => 'Deskripsi2 CPL',
            'prodi_id' => $this->user->prodi_id,
        ]);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpl', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data CPL berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    /**
     * Test mengambil detail satu CPL berdasarkan ID.
     */
    public function test_view_detail_data_cpl_berdasarkan_id(): void
    {
        $cpl = Cpl::factory()->create([
            'kode_cpl' => 'CPL999',
            'nama_cpl' => 'Nama CPL',
            'deskripsi' => 'Deskripsi CPL',
            'prodi_id' => $this->user->prodi_id,
        ]);

        $payload = [
            'action' => 'view',
            'cpl_id' => $cpl->cpl_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpl', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPL berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($cpl->cpl_id, $data['cpl_id']);
        $this->assertEquals($cpl->prodi_id, $data['prodi_id']);
    }

    /**
     * Test pembuatan data CPL berhasil.
     */
    public function test_store_data_cpl_berhasil(): void
    {
        $payload = [
            'action' => 'store',
            'kode_cpl' => 'CPL999',
            'nama_cpl' => 'Store CPL',
            'deskripsi' => 'Deskripsi Store CPL',
            'prodi_id' => $this->user->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpl', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data CPL berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('cpl', [
            'kode_cpl' => 'CPL999',
            'prodi_id' => $this->user->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat pembuatan data CPL.
     */
    public function test_store_data_cpl_validasi_gagal(): void
    {
        $payload = [
            'action' => 'store',
            'kode_cpl' => '',
            'nama_cpl' => '',
            'deskripsi' => '',
            'prodi_id' => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpl', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_cpl', 'nama_cpl', 'prodi_id']);
    }

    /**
     * Test update data CPL berhasil.
     */
    public function test_update_data_cpl_berhasil(): void
    {
        $cpl = Cpl::factory()->create([
            'kode_cpl' => 'CPL000',
            'nama_cpl' => 'Update CPL',
            'deskripsi' => 'Deskripsi Update CPL',
            'prodi_id' => $this->user->prodi_id,
        ]);

        $updatePayload = [
            'action' => 'update',
            'cpl_id' => $cpl->cpl_id,
            'kode_cpl' => 'CPL888',
            'nama_cpl' => 'Updated CPL',
            'deskripsi' => 'Deskripsi Updated CPL',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpl', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPL berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('cpl', [
            'cpl_id' => $cpl->cpl_id,
            'kode_cpl' => 'CPL888',
            'nama_cpl' => 'Updated CPL',
            'deskripsi' => 'Deskripsi Updated CPL',
        ]);
    }

    /**
     * Test validasi gagal saat update data CPL.
     */
    public function test_update_data_cpl_validasi_gagal(): void
    {
        $cpl = Cpl::factory()->create([
            'kode_cpl' => 'CPL111',
            'nama_cpl' => 'Update gagal CPL',
            'deskripsi' => 'Deskripsi Update gagal CPL',
            'prodi_id' => $this->user->prodi_id,
        ]);

        $invalidPayload = [
            'action' => 'update',
            'cpl_id' => $cpl->cpl_id,
            'kode_cpl' => '',         // Kosong agar gagal validasi
            'nama_cpl' => '',         // Kosong agar gagal validasi
            'deskripsi' => '',        // Kosong agar gagal validasi
            'prodi_id' => 9999,       // ID prodi tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpl', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_cpl', 'nama_cpl', 'prodi_id']);
    }

    /**
     * Test penghapusan data CPL berhasil.
     */
    public function test_delete_data_cpl_berhasil(): void
    {
        $cpl = Cpl::factory()->create([
            'kode_cpl' => 'CPL222',
            'nama_cpl' => 'Delete CPL',
            'deskripsi' => 'Deskripsi Delete CPL',
            'prodi_id' => $this->user->prodi_id,
        ]);

        $payload = [
            'action' => 'delete',
            'cpl_id' => $cpl->cpl_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpl', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPL berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('cpl', [
            'cpl_id' => $cpl->cpl_id,
        ]);
    }
}
