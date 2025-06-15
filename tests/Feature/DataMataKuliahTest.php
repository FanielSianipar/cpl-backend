<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataMataKuliahTest extends TestCase
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

        // Pastikan ada data Fakultas dan Prodi untuk operasi mata kuliah
        $fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK100',
            'nama_fakultas' => 'Pertambangan',
        ]);

        $this->prodi = Prodi::factory()->create([
            'kode_prodi' => 'PRD100',
            'nama_prodi' => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        // Buat role Admin Prodi dengan izin mengelola mata kuliah
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create([
            'prodi_id' => $this->prodi->prodi_id
        ]);
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);
    }

    /**
     * Test mengambil seluruh data mata kuliah.
     */
    public function test_view_all_data_mata_kuliah(): void
    {
        MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK444',
            'nama_mata_kuliah' => 'Nama1 Mata Kuliah',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK555',
            'nama_mata_kuliah' => 'Nama2 Mata Kuliah',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mata-kuliah', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data mata kuliah berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    /**
     * Test mengambil detail satu mata kuliah berdasarkan ID.
     */
    public function test_view_detail_data_mata_kuliah_berdasarkan_id(): void
    {
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK999',
            'nama_mata_kuliah' => 'Nama Mata Kuliah',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $payload = [
            'action' => 'view',
            'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mata-kuliah', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($mataKuliah->mata_kuliah_id, $data['mata_kuliah_id']);
        $this->assertEquals($mataKuliah->prodi_id, $data['prodi_id']);
    }

    /**
     * Test pembuatan data mata kuliah berhasil.
     */
    public function test_store_data_mata_kuliah_berhasil(): void
    {
        $payload = [
            'action' => 'store',
            'kode_mata_kuliah' => 'MK999',
            'nama_mata_kuliah' => 'Store Mata Kuliah',
            'prodi_id' => $this->prodi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mata-kuliah', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('mata_kuliah', [
            'kode_mata_kuliah' => 'MK999',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat pembuatan data mata kuliah.
     */
    public function test_store_data_mata_kuliah_validasi_gagal(): void
    {
        $payload = [
            'action' => 'store',
            'kode_mata_kuliah' => '',
            'nama_mata_kuliah' => '',
            'prodi_id' => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mata-kuliah', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_mata_kuliah', 'nama_mata_kuliah', 'prodi_id']);
    }

    /**
     * Test update data mata kuliah berhasil.
     */
    public function test_update_data_mata_kuliah_berhasil(): void
    {
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK000',
            'nama_mata_kuliah' => 'Update Mata Kuliah',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        // Jika ingin update ke prodi yang berbeda, buat terlebih dahulu record prodi baru
        $newProdi = Prodi::factory()->create(['nama_prodi' => 'Sistem Informasi']);

        $updatePayload = [
            'action' => 'update',
            'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
            'kode_mata_kuliah' => 'MK888',
            'nama_mata_kuliah' => 'Updated Mata Kuliah',
            'prodi_id' => $newProdi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mata-kuliah', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('mata_kuliah', [
            'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
            'kode_mata_kuliah' => 'MK888',
            'nama_mata_kuliah' => 'Updated Mata Kuliah',
            'prodi_id' => $newProdi->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat update data mata kuliah.
     */
    public function test_update_data_mata_kuliah_validasi_gagal(): void
    {
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK111',
            'nama_mata_kuliah' => 'Update gagal Mata Kuliah',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $invalidPayload = [
            'action' => 'update',
            'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
            'kode_mata_kuliah' => '',         // Kosong agar gagal validasi
            'nama_mata_kuliah' => '',         // Kosong agar gagal validasi
            'prodi_id' => 9999,               // ID prodi tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mata-kuliah', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_mata_kuliah', 'nama_mata_kuliah', 'prodi_id']);
    }

    /**
     * Test penghapusan data mata kuliah berhasil.
     */
    public function test_delete_data_mata_kuliah_berhasil(): void
    {
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK222',
            'nama_mata_kuliah' => 'Delete Mata Kuliah',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $payload = [
            'action' => 'delete',
            'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mata-kuliah', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('mata_kuliah', [
            'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
        ]);
    }
}
