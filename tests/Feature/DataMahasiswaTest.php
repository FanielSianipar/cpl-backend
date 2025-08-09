<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataMahasiswaTest extends TestCase
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

        // Buat data Fakultas dan Prodi untuk operasi mahasiswa
        $fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK100',
            'nama_fakultas' => 'Pertambangan',
        ]);

        $this->prodi = Prodi::factory()->create([
            'kode_prodi' => 'PRD100',
            'nama_prodi' => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        // Buat role Admin Prodi yang memiliki izin mengelola mahasiswa
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create([
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);
    }

    /**
     * Test mengambil seluruh data mahasiswa.
     */
    public function test_view_all_data_mahasiswa(): void
    {
        // Buat beberapa data Mahasiswa
        Mahasiswa::factory()->create([
            'npm'      => '201506444',
            'name'     => 'Nama1 Mahasiswa',
            'angkatan' => 2020,
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        Mahasiswa::factory()->create([
            'npm'      => '201506555',
            'name'     => 'Nama2 Mahasiswa',
            'angkatan' => 2020,
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mahasiswa', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data mahasiswa berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    /**
     * Test mengambil detail satu mahasiswa berdasarkan ID.
     */
    public function test_view_detail_data_mahasiswa_berdasarkan_id(): void
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506999',
            'name'     => 'Nama Mahasiswa',
            'angkatan' => 2020,
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $payload = [
            'action' => 'view',
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mahasiswa', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil diambil.',
            ]);

        $data = $response->json('data');
        $this->assertEquals($mahasiswa->mahasiswa_id, $data['mahasiswa_id']);
    }

    /**
     * Test pembuatan data mahasiswa berhasil.
     */
    public function test_store_data_mahasiswa_berhasil(): void
    {
        $payload = [
            'action'   => 'store',
            'npm'      => '201506999',
            'name'     => 'Store Mahasiswa',
            'angkatan' => 2020,
            'prodi_id' => $this->prodi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mahasiswa', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('mahasiswa', [
            'npm'      => '201506999',
            'prodi_id' => $this->prodi->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat pembuatan data mahasiswa.
     */
    public function test_store_data_mahasiswa_validasi_gagal(): void
    {
        $payload = [
            'action'   => 'store',
            'npm'      => '',
            'name'     => '',
            'angkatan' => '',
            'prodi_id' => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mahasiswa', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['npm', 'name', 'angkatan', 'prodi_id']);
    }

    /**
     * Test update data mahasiswa berhasil.
     */
    public function test_update_data_mahasiswa_berhasil(): void
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506000',
            'name'     => 'Update Mahasiswa',
            'angkatan' => 2020,
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        // Jika ingin update ke prodi yang berbeda, buat terlebih dahulu record prodi baru
        $newProdi = Prodi::factory()->create(['nama_prodi' => 'Sistem Informasi']);

        $updatePayload = [
            'action'      => 'update',
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'npm'         => '201506888',
            'name'        => 'Updated Mahasiswa',
            'angkatan'   => 2021,
            'prodi_id'    => $newProdi->prodi_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mahasiswa', $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('mahasiswa', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'npm'      => '201506888',
            'name'     => 'Updated Mahasiswa',
            'prodi_id' => $newProdi->prodi_id,
        ]);
    }

    /**
     * Test validasi gagal saat update data mahasiswa.
     */
    public function test_update_data_mahasiswa_validasi_gagal(): void
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506111',
            'name'     => 'Update gagal Mahasiswa',
            'angkatan' => 2020,
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $invalidPayload = [
            'action'      => 'update',
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'npm'         => '',         // Kosong agar gagal validasi
            'name'        => '',         // Kosong agar gagal validasi
            'angkatan'   => '',         // Kosong agar gagal validasi
            'prodi_id'    => 9999,       // ID prodi tidak valid
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mahasiswa', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['npm', 'name', 'angkatan', 'prodi_id']);
    }

    /**
     * Test penghapusan data mahasiswa berhasil.
     */
    public function test_delete_data_mahasiswa_berhasil(): void
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506222',
            'name'     => 'Delete Mahasiswa',
            'angkatan' => 2020,
            'prodi_id' => $this->prodi->prodi_id,
        ]);

        $payload = ['action' => 'delete', 'mahasiswa_id' => $mahasiswa->mahasiswa_id];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-mahasiswa', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('mahasiswa', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ]);
    }
}
