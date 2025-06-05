<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataMataKuliahTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada satu data Fakultas dan Prodi untuk semua operasi mata kuliah
        $fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK100',
            'nama_fakultas' => 'Pertambangan',
        ]);
        Prodi::factory()->create([
            'kode_prodi' => 'PRD100',
            'nama_prodi' => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);
    }

    /**
     * Test untuk menampilkan seluruh data Mata Kuliah.
     */
    public function test_view_all_data_mata_kuliah(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mata kuliah');

        $prodi = Prodi::first();

        // Buat beberapa data Mata Kuliah.
        MataKuliah::factory()->create([
            'kode_mata_kuliah'      => 'kode_mk444',
            'nama_mata_kuliah'     => 'Nama1 Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ]);
        MataKuliah::factory()->create([
            'kode_mata_kuliah'      => 'kode_mk555',
            'nama_mata_kuliah'     => 'Nama2 Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view'
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mata-kuliah', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data mata kuliah berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    /**
     * Test untuk mengambil detail satu data Mata Kuliah berdasarkan ID.
     */
    public function test_view_detail_data_mata_kuliah_berdasarkan_id(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mata kuliah');

        $prodi = Prodi::first();
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah'      => 'kode_mk999',
            'nama_mata_kuliah'     => 'Nama Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view',
            'mata_kuliah_id'     => $mataKuliah->mata_kuliah_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mata-kuliah', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertEquals($mataKuliah->mata_kuliah_id, $responseData['mata_kuliah_id']);
        $this->assertEquals($mataKuliah->prodi_id, $responseData['prodi_id']);
    }

    /**
     * Test untuk menyimpan data Mata Kuliah baru.
     */
    public function test_store_data_mata_kuliah_berhasil(): void
    {
        $prodi = Prodi::first();

        // Buat role dan permission untuk user yang melakukan operasi
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mata kuliah');

        $payload = [
            'action'      => 'store',
            'kode_mata_kuliah'      => 'kode_mk999',
            'nama_mata_kuliah'     => 'Store Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mata-kuliah', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('mata_kuliah', [
            'kode_mata_kuliah'  => 'kode_mk999',
            'prodi_id' => $prodi->prodi_id,
        ]);
    }

    /**
     * Test untuk store data mata kuliah gagal karena validasi.
     */
    public function test_store_data_mata_kuliah_validasi_gagal(): void
    {
        // Atur role dan permission untuk user yang melakukan operasi.
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mata kuliah');

        // Buat payload dengan data yang tidak valid: kosong atau tidak sesuai
        $payload = [
            'action'      => 'store',
            'kode_mata_kuliah'  => '',   // kosong, sehingga gagal validasi
            'nama_mata_kuliah'  => '',   // kosong, sehingga gagal validasi
            'prodi_id' => '',   // kosong, sehingga gagal validasi
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mata-kuliah', $payload);

        // Harapkan status 422 beserta error validasi untuk field terkait
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_mata_kuliah', 'nama_mata_kuliah', 'prodi_id']);
    }

    /**
     * Test untuk update data Mata Kuliah.
     */
    public function test_update_data_mata_kuliah_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mata kuliah');

        $prodi = Prodi::first();
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah'      => 'kode_mk000',
            'nama_mata_kuliah'     => 'Update Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action'      => 'update',
            'mata_kuliah_id'    => $mataKuliah->mata_kuliah_id,
            'kode_mata_kuliah'      => 'kode_mk888',
            'nama_mata_kuliah'     => 'Updated Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mata-kuliah', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil diperbarui.'
            ]);

        $this->assertDatabaseHas('mata_kuliah', [
            'mata_kuliah_id'   => $mataKuliah->mata_kuliah_id,
            'kode_mata_kuliah'      => 'kode_mk888',
            'nama_mata_kuliah'     => 'Updated Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ]);
    }

    /**
     * Test untuk update data mata kuliah gagal karena validasi.
     */
    public function test_update_data_mata_kuliah_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mata kuliah');

        $prodi = Prodi::first();

        // Buat data mata kuliah awal menggunakan factory.
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah'      => 'kode_mk111',
            'nama_mata_kuliah'     => 'Update gagal Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ]);

        // Buat payload update dengan data yang tidak valid
        $payload = [
            'action'      => 'update',
            'mata_kuliah_id'    => $mataKuliah->mata_kuliah_id,
            'kode_mata_kuliah'  => '',         // kosong agar gagal validasi
            'nama_mata_kuliah'  => '',         // kosong agar gagal validasi
            'prodi_id' => 9999,       // misalnya, id prodi tidak ada
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mata-kuliah', $payload);

        // Harapkan status 422 dan validasi error untuk ketiga field
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_mata_kuliah', 'nama_mata_kuliah', 'prodi_id']);
    }

    /**
     * Test untuk menghapus data Mata Kuliah.
     */
    public function test_delete_data_mata_kuliah_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mata kuliah', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mata kuliah');

        $prodi = Prodi::first();
        $mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah'      => 'kode_mk222',
            'nama_mata_kuliah'     => 'Delete Mata Kuliah',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'delete',
            'mata_kuliah_id'     => $mataKuliah->mata_kuliah_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mata-kuliah', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mata kuliah berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('mata_kuliah', [
            'mata_kuliah_id' => $mataKuliah->mata_kuliah_id,
        ]);
    }
}
