<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataProdiTest extends TestCase
{
    use RefreshDatabase;

    protected $fakultas;
    protected $adminUniversitasRole;
    protected $permission;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat data Fakultas sebagai acuan
        $this->fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK01',
            'nama_fakultas' => 'Fakultas Teknik',
        ]);

        // Setup role dan permission global untuk operasi Prodi
        $this->adminUniversitasRole = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        // Buat user acting (Admin Universitas) dan berikan role serta permission
        $this->user = User::factory()->create();
        $this->user->assignRole($this->adminUniversitasRole);
        $this->adminUniversitasRole->givePermissionTo($this->permission);
    }

    /**
     * Test untuk menampilkan seluruh data Prodi.
     */
    public function test_view_all_data_prodi(): void
    {
        // Buat beberapa data Prodi
        Prodi::factory()->create([
            'kode_prodi'  => 'PRD001',
            'nama_prodi'  => 'Teknik Informatika',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ]);
        Prodi::factory()->create([
            'kode_prodi'  => 'PRD002',
            'nama_prodi'  => 'Sistem Informasi',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ]);

        $payload = [
            'action' => 'view'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data prodi berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    /**
     * Test untuk mengambil detail satu data Prodi berdasarkan ID.
     */
    public function test_view_detail_data_prodi_berdasarkan_id(): void
    {
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD003',
            'nama_prodi'  => 'Teknik Elektro',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ]);

        $payload = [
            'action'   => 'view',
            'prodi_id' => $prodi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data prodi berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertEquals($prodi->prodi_id, $responseData['prodi_id']);
        $this->assertEquals($prodi->kode_prodi, $responseData['kode_prodi']);
    }

    /**
     * Test untuk menyimpan data Prodi baru.
     */
    public function test_store_data_prodi_berhasil(): void
    {
        $payload = [
            'action'      => 'store',
            'kode_prodi'  => 'PRD001',
            'nama_prodi'  => 'Teknik Informatika',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data prodi berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('prodi', [
            'kode_prodi'  => 'PRD001',
            'nama_prodi'  => 'Teknik Informatika',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ]);
    }

    /**
     * Test untuk store data prodi gagal karena validasi.
     */
    public function test_store_data_prodi_validasi_gagal(): void
    {
        $payload = [
            'action'      => 'store',
            'kode_prodi'  => '',   // kosong, sehingga gagal validasi
            'nama_prodi'  => '',   // kosong, sehingga gagal validasi
            'fakultas_id' => '',   // tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_prodi', 'nama_prodi', 'fakultas_id']);
    }

    /**
     * Test untuk update data Prodi.
     */
    public function test_update_data_prodi_berhasil(): void
    {
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD004',
            'nama_prodi'  => 'Teknik Mesin',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ]);

        $payload = [
            'action'      => 'update',
            'prodi_id'    => $prodi->prodi_id,
            'kode_prodi'  => 'PRD004', // tetap atau diubah
            'nama_prodi'  => 'Teknik Mesin Terbaru',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data prodi berhasil diperbarui.'
            ]);

        $this->assertDatabaseHas('prodi', [
            'prodi_id'   => $prodi->prodi_id,
            'nama_prodi' => 'Teknik Mesin Terbaru',
        ]);
    }

    /**
     * Test untuk update data prodi gagal karena validasi.
     */
    public function test_update_data_prodi_validasi_gagal(): void
    {
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD006',
            'nama_prodi'  => 'Teknik Sipil',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ]);

        $payload = [
            'action'      => 'update',
            'prodi_id'    => $prodi->prodi_id,
            'kode_prodi'  => '',         // harus diisi
            'nama_prodi'  => '',         // harus diisi
            'fakultas_id' => 9999,        // ID fakultas tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_prodi', 'nama_prodi', 'fakultas_id']);
    }

    /**
     * Test untuk menghapus data Prodi.
     */
    public function test_delete_data_prodi_berhasil(): void
    {
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD005',
            'nama_prodi'  => 'Teknik Industri',
            'fakultas_id' => $this->fakultas->fakultas_id,
        ]);

        $payload = [
            'action'   => 'delete',
            'prodi_id' => $prodi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data prodi berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('prodi', [
            'prodi_id' => $prodi->prodi_id,
        ]);
    }
}
