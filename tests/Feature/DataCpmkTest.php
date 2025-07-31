<?php

namespace Tests\Feature;

use App\Models\CPMK;
use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataCpmkTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminProdiRole;
    protected $permission;
    protected $prodi;
    protected $mataKuliah;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada data Fakultas dan Prodi untuk operasi CPMK
        $fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK100',
            'nama_fakultas' => 'Pertambangan',
        ]);

        $this->prodi = Prodi::factory()->create([
            'kode_prodi' => 'PRD100',
            'nama_prodi' => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        $this->mataKuliah = MataKuliah::factory()->create([
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        // Buat role Admin Prodi dengan izin mengelola CPMK
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create();
        $this->user->prodi_id = $this->prodi->prodi_id;
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);
    }

    /**
     * Test mengambil seluruh data CPMK.
     */
    public function test_view_all_data_cpmk(): void
    {
        CPMK::factory()->create([
            'kode_cpmk' => 'CPMK444',
            'nama_cpmk' => 'Nama1 CPMK',
            'deskripsi' => 'Deskripsi1 CPMK',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ]);
        CPMK::factory()->create([
            'kode_cpmk' => 'CPMK555',
            'nama_cpmk' => 'Nama2 CPMK',
            'deskripsi' => 'Deskripsi2 CPMK',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data CPMK berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    /**
     * Test mengambil detail CPMK berdasarkan ID.
     */
    public function test_view_detail_data_cpmk_berdasarkan_id(): void
    {
        $cpmk = CPMK::factory()->create([
            'kode_cpmk' => 'CPMK999',
            'nama_cpmk' => 'Nama CPMK',
            'deskripsi' => 'Deskripsi CPMK',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = [
            'action'  => 'view',
            'cpmk_id' => $cpmk->cpmk_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPMK berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($cpmk->cpmk_id, $data['cpmk_id']);
        $this->assertEquals($cpmk->mata_kuliah_id, $data['mata_kuliah_id']);
    }

    /**
     * Test menyimpan data CPMK berhasil.
     */
    public function test_store_data_cpmk_berhasil(): void
    {
        $payload = [
            'action'    => 'store',
            'kode_cpmk' => 'CPMK999',
            'nama_cpmk' => 'Store CPMK',
            'deskripsi' => 'Deskripsi Store CPMK',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data CPMK berhasil dibuat.',
            ]);

        $this->assertDatabaseHas('cpmk', [
            'kode_cpmk' => 'CPMK999',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ]);
    }

    /**
     * Test validasi gagal saat penyimpanan CPMK.
     */
    public function test_store_data_cpmk_validasi_gagal(): void
    {
        $payload = [
            'action'    => 'store',
            'kode_cpmk' => '',
            'nama_cpmk' => '',
            'deskripsi' => '',
            'mata_kuliah_id'  => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'kode_cpmk',
                'nama_cpmk',
                'mata_kuliah_id',
            ]);
    }

    /**
     * Test update data CPMK berhasil.
     */
    public function test_update_data_cpmk_berhasil(): void
    {
        $cpmk = CPMK::factory()->create([
            'kode_cpmk' => 'CPMK000',
            'nama_cpmk' => 'CPMK Lama',
            'deskripsi' => 'Deskripsi Lama',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = [
            'action'    => 'update',
            'cpmk_id'   => $cpmk->cpmk_id,  // gunakan cpmk_id
            'kode_cpmk' => 'CPMK888',
            'nama_cpmk' => 'CPMK Baru',
            'deskripsi' => 'Deskripsi Baru',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPMK berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('cpmk', [
            'cpmk_id'   => $cpmk->cpmk_id,
            'kode_cpmk' => 'CPMK888',
            'nama_cpmk' => 'CPMK Baru',
        ]);
    }

    /**
     * Test validasi gagal saat update CPMK.
     */
    public function test_update_data_cpmk_validasi_gagal(): void
    {
        $cpmk = CPMK::factory()->create([
            'kode_cpmk' => 'CPMK111',
            'nama_cpmk' => 'CPMK Awal',
            'deskripsi' => 'Deskripsi Awal',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = [
            'action'    => 'update',
            'cpmk_id'   => $cpmk->cpmk_id,
            'kode_cpmk' => '',
            'nama_cpmk' => '',
            'deskripsi' => '',
            'mata_kuliah_id'  => '', // mata_kuliah_id tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'kode_cpmk',
                'nama_cpmk',
                'mata_kuliah_id',
            ]);
    }

    /**
     * Test penghapusan data CPMK berhasil.
     */
    public function test_delete_data_cpmk_berhasil(): void
    {
        $cpmk = CPMK::factory()->create([
            'kode_cpmk' => 'CPMK222',
            'nama_cpmk' => 'CPMK Hapus',
            'deskripsi' => 'Deskripsi Hapus',
            'mata_kuliah_id'  => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = [
            'action'  => 'delete',
            'cpmk_id' => $cpmk->cpmk_id,  // gunakan cpmk_id
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPMK berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('cpmk', [
            'cpmk_id' => $cpmk->cpmk_id,
        ]);
    }
}
