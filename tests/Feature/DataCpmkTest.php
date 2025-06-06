<?php

namespace Tests\Feature;

use App\Models\CPMK;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataCpmkTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada satu data Fakultas dan Prodi untuk semua operasi CPMK
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
     * Test untuk menampilkan seluruh data CPMK.
     */
    public function test_view_all_data_cpmk(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPMK');

        $prodi = Prodi::first();

        // Buat beberapa data CPMK.
        CPMK::factory()->create([
            'kode_cpmk'      => 'k_cpmk444',
            'nama_cpmk'     => 'Nama1 CPMK',
            'deskripsi'     => 'Deskripsi1 CPMK',
            'prodi_id' => $prodi->prodi_id
        ]);
        CPMK::factory()->create([
            'kode_cpmk'      => 'k_cpmk555',
            'nama_cpmk'     => 'Nama2 CPMK',
            'deskripsi'     => 'Deskripsi2 CPMK',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view'
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data CPMK berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    /**
     * Test untuk mengambil detail satu data CPMK berdasarkan ID.
     */
    public function test_view_detail_data_cpmk_berdasarkan_id(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPMK');

        $prodi = Prodi::first();
        $cpmk = CPMK::factory()->create([
            'kode_cpmk'      => 'k_cpmk999',
            'nama_cpmk'     => 'Nama CPMK',
            'deskripsi'     => 'Deskripsi3 CPMK',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view',
            'cpmk_id'     => $cpmk->cpmk_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPMK berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertEquals($cpmk->cpmk_id, $responseData['cpmk_id']);
        $this->assertEquals($cpmk->prodi_id, $responseData['prodi_id']);
    }

    /**
     * Test untuk menyimpan data CPMK baru.
     */
    public function test_store_data_cpmk_berhasil(): void
    {
        $prodi = Prodi::first();

        // Buat role dan permission untuk user yang melakukan operasi
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPMK');

        $payload = [
            'action'      => 'store',
            'kode_cpmk'      => 'k_cpmk999',
            'nama_cpmk'     => 'Store CPMK',
            'deskripsi'     => 'Deskripsi Store CPMK',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpmk', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data CPMK berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('cpmk', [
            'kode_cpmk'  => 'k_cpmk999',
            'prodi_id' => $prodi->prodi_id,
        ]);
    }

    /**
     * Test untuk store data CPMK gagal karena validasi.
     */
    public function test_store_data_cpmk_validasi_gagal(): void
    {
        // Atur role dan permission untuk user yang melakukan operasi.
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPMK');

        // Buat payload dengan data yang tidak valid: kosong atau tidak sesuai
        $payload = [
            'action'      => 'store',
            'kode_cpmk'  => '',   // kosong, sehingga gagal validasi
            'nama_cpmk'  => '',   // kosong, sehingga gagal validasi
            'deskripsi'  => '',   // kosong, sehingga gagal validasi
            'prodi_id' => '',   // kosong, sehingga gagal validasi
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpmk', $payload);

        // Harapkan status 422 beserta error validasi untuk field terkait
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_cpmk', 'nama_cpmk', 'prodi_id']);
    }

    /**
     * Test untuk update data CPMK.
     */
    public function test_update_data_cpmk_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPMK');

        $prodi = Prodi::first();
        $cpmk = CPMK::factory()->create([
            'kode_cpmk'      => 'k_cpmk000',
            'nama_cpmk'     => 'Update CPMK',
            'deskripsi'     => 'Deskripsi Update CPMK',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action'      => 'update',
            'cpmk_id'    => $cpmk->cpmk_id,
            'kode_cpmk'      => 'k_cpmk888',
            'nama_cpmk'     => 'Updated CPMK',
            'deskripsi'     => 'Deskripsi Updated CPMK',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPMK berhasil diperbarui.'
            ]);

        $this->assertDatabaseHas('cpmk', [
            'cpmk_id'   => $cpmk->cpmk_id,
            'kode_cpmk'      => 'k_cpmk888',
            'nama_cpmk'     => 'Updated CPMK',
            'prodi_id' => $prodi->prodi_id
        ]);
    }

    /**
     * Test untuk update data CPMK gagal karena validasi.
     */
    public function test_update_data_cpmk_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPMK');

        $prodi = Prodi::first();

        // Buat data CPMK awal menggunakan factory.
        $cpmk = CPMK::factory()->create([
            'kode_cpmk'      => 'k_cpmk111',
            'nama_cpmk'     => 'Update gagal CPMK',
            'deskripsi'     => 'Deskripsi Update gagal CPMK',
            'prodi_id' => $prodi->prodi_id
        ]);

        // Buat payload update dengan data yang tidak valid
        $payload = [
            'action'      => 'update',
            'cpmk_id'    => $cpmk->cpmk_id,
            'kode_cpmk'  => '',         // kosong agar gagal validasi
            'nama_cpmk'  => '',         // kosong agar gagal validasi
            'deskripsi'  => '',         // kosong agar gagal validasi
            'prodi_id' => 9999,       // misalnya, id prodi tidak ada
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpmk', $payload);

        // Harapkan status 422 dan validasi error untuk ketiga field
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_cpmk', 'nama_cpmk', 'prodi_id']);
    }

    /**
     * Test untuk menghapus data CPMK.
     */
    public function test_delete_data_cpmk_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPMK', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPMK');

        $prodi = Prodi::first();
        $cpmk = CPMK::factory()->create([
            'kode_cpmk'      => 'k_cpmk222',
            'nama_cpmk'     => 'Delete CPMK',
            'deskripsi'     => 'Deskripsi Delete CPMK',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'delete',
            'cpmk_id'     => $cpmk->cpmk_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPMK berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('cpmk', [
            'cpmk_id' => $cpmk->cpmk_id,
        ]);
    }
}
