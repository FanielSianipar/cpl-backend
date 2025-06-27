<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataKelasTest extends TestCase
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

        // Pastikan ada data Fakultas dan Prodi
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
            'kode_mata_kuliah' => 'PRD100',
            'nama_mata_kuliah' => 'Pertambangan Batu Bara',
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        // Buat role Admin Prodi dengan izin mengelola kelas
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data kelas', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create([
            'prodi_id' => $this->prodi->prodi_id
        ]);
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);
    }

    /**
     * Test mengambil seluruh data kelas.
     */
    public function test_view_all_data_kelas(): void
    {
        Kelas::factory()->create([
            'kode_kelas' => 'K444',
            'nama_kelas' => 'Nama1 Kelas',
            'semester' => 1,
            'tahun_ajaran' => 'tahun1',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        Kelas::factory()->create([
            'kode_kelas' => 'K555',
            'nama_kelas' => 'Nama2 Kelas',
            'semester' => 2,
            'tahun_ajaran' => 'tahun2',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data kelas berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    /**
     * Test mengambil detail satu kelas berdasarkan ID.
     */
    public function test_view_detail_data_kelas_berdasarkan_id(): void
    {
        $kelas = Kelas::factory()->create([
            'kode_kelas' => 'K999',
            'nama_kelas' => 'Nama Kelas',
            'semester' => 1,
            'tahun_ajaran' => 'tahun1',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = [
            'action' => 'view',
            'kelas_id' => $kelas->kelas_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data kelas berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($kelas->kelas_id, $data['kelas_id']);
        $this->assertEquals($kelas->mata_kuliah_id, $data['mata_kuliah_id']);
    }

    /**
     * Test pembuatan data kelas berhasil.
     */
    public function test_store_data_kelas_berhasil(): void
    {
        $payload = [
            'action' => 'store',
            'kode_kelas' => 'K999',
            'nama_kelas' => 'Store Kelas',
            'semester' => 1,
            'tahun_ajaran' => 'tahun1',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data kelas berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('kelas', [
            'kode_kelas' => 'K999',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
    }

    /**
     * Test validasi gagal saat pembuatan data kelas.
     */
    public function test_store_data_kelas_validasi_gagal(): void
    {
        $payload = [
            'action' => 'store',
            'kode_kelas' => '',
            'nama_kelas' => '',
            'semester' => '',
            'tahun_ajaran' => '',
            'mata_kuliah_id' => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_kelas', 'nama_kelas', 'semester', 'tahun_ajaran', 'mata_kuliah_id']);
    }

    /**
     * Test update data kelas berhasil.
     */
    public function test_update_data_kelas_berhasil(): void
    {
        $kelas = Kelas::factory()->create([
            'kode_kelas' => 'K000',
            'nama_kelas' => 'Update Kelas',
            'semester' => 1,
            'tahun_ajaran' => 'tahun1',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        $updatePayload = [
            'action' => 'update',
            'kelas_id' => $kelas->kelas_id,
            'kode_kelas' => 'K888',
            'nama_kelas' => 'Updated Kelas',
            'semester' => 2,
            'tahun_ajaran' => 'tahun2',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data kelas berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('kelas', [
            'kelas_id' => $kelas->kelas_id,
            'kode_kelas' => 'K888',
            'nama_kelas' => 'Updated Kelas',
            'semester' => 2,
            'tahun_ajaran' => 'tahun2',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
    }

    /**
     * Test validasi gagal saat update data kelas.
     */
    public function test_update_data_kelas_validasi_gagal(): void
    {
        $kelas = Kelas::factory()->create([
            'kode_kelas' => 'K111',
            'nama_kelas' => 'Update gagal Kelas',
            'semester' => 1,
            'tahun_ajaran' => 'tahun1',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        $invalidPayload = [
            'action' => 'update',
            'kelas_id' => $kelas->kelas_id,
            'kode_kelas' => '',         // Kosong agar gagal validasi
            'nama_kelas' => '',         // Kosong agar gagal validasi
            'semester' => '',            // Tidak valid
            'tahun_ajaran' => '',       // Kosong agar gagal validasi
            'mata_kuliah_id' => 9999,               // ID mata kuliah tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_kelas', 'nama_kelas', 'semester', 'tahun_ajaran', 'mata_kuliah_id']);
    }

    /**
     * Test penghapusan data kelas berhasil.
     */
    public function test_delete_data_kelas_berhasil(): void
    {
        $kelas = Kelas::factory()->create([
            'kode_kelas' => 'K222',
            'nama_kelas' => 'Delete Kelas',
            'semester' => 1,
            'tahun_ajaran' => 'tahun1',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = [
            'action' => 'delete',
            'kelas_id' => $kelas->kelas_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data kelas berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('kelas', [
            'kelas_id' => $kelas->kelas_id,
        ]);
    }
}
