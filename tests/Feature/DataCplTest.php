<?php

namespace Tests\Feature;

use App\Models\CPL;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataCplTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada satu data Fakultas dan Prodi untuk semua operasi CPL
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
     * Test untuk menampilkan seluruh data CPL.
     */
    public function test_view_all_data_cpl(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPL');

        $prodi = Prodi::first();

        // Buat beberapa data CPL.
        CPL::factory()->create([
            'kode_cpl'      => 'k_cpl444',
            'nama_cpl'     => 'Nama1 CPL',
            'deskripsi'     => 'Deskripsi1 CPL',
            'prodi_id' => $prodi->prodi_id
        ]);
        CPL::factory()->create([
            'kode_cpl'      => 'k_cpl555',
            'nama_cpl'     => 'Nama2 CPL',
            'deskripsi'     => 'Deskripsi2 CPL',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view'
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data CPL berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    /**
     * Test untuk mengambil detail satu data CPL berdasarkan ID.
     */
    public function test_view_detail_data_cpl_berdasarkan_id(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPL');

        $prodi = Prodi::first();
        $cpl = CPL::factory()->create([
            'kode_cpl'      => 'k_cpl999',
            'nama_cpl'     => 'Nama CPL',
            'deskripsi'     => 'Deskripsi3 CPL',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view',
            'cpl_id'     => $cpl->cpl_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPL berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertEquals($cpl->cpl_id, $responseData['cpl_id']);
        $this->assertEquals($cpl->prodi_id, $responseData['prodi_id']);
    }

    /**
     * Test untuk menyimpan data CPL baru.
     */
    public function test_store_data_cpl_berhasil(): void
    {
        $prodi = Prodi::first();

        // Buat role dan permission untuk user yang melakukan operasi
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPL');

        $payload = [
            'action'      => 'store',
            'kode_cpl'      => 'k_cpl999',
            'nama_cpl'     => 'Store CPL',
            'deskripsi'     => 'Deskripsi Store CPL',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpl', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data CPL berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('cpl', [
            'kode_cpl'  => 'k_cpl999',
            'prodi_id' => $prodi->prodi_id,
        ]);
    }

    /**
     * Test untuk store data CPL gagal karena validasi.
     */
    public function test_store_data_cpl_validasi_gagal(): void
    {
        // Atur role dan permission untuk user yang melakukan operasi.
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPL');

        // Buat payload dengan data yang tidak valid: kosong atau tidak sesuai
        $payload = [
            'action'      => 'store',
            'kode_cpl'  => '',   // kosong, sehingga gagal validasi
            'nama_cpl'  => '',   // kosong, sehingga gagal validasi
            'deskripsi'  => '',   // kosong, sehingga gagal validasi
            'prodi_id' => '',   // kosong, sehingga gagal validasi
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpl', $payload);

        // Harapkan status 422 beserta error validasi untuk field terkait
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_cpl', 'nama_cpl', 'prodi_id']);
    }

    /**
     * Test untuk update data CPL.
     */
    public function test_update_data_cpl_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPL');

        $prodi = Prodi::first();
        $cpl = CPL::factory()->create([
            'kode_cpl'      => 'k_cpl000',
            'nama_cpl'     => 'Update CPL',
            'deskripsi'     => 'Deskripsi Update CPL',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action'      => 'update',
            'cpl_id'    => $cpl->cpl_id,
            'kode_cpl'      => 'k_cpl888',
            'nama_cpl'     => 'Updated CPL',
            'deskripsi'     => 'Deskripsi Updated CPL',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPL berhasil diperbarui.'
            ]);

        $this->assertDatabaseHas('cpl', [
            'cpl_id'   => $cpl->cpl_id,
            'kode_cpl'      => 'k_cpl888',
            'nama_cpl'     => 'Updated CPL',
            'prodi_id' => $prodi->prodi_id
        ]);
    }

    /**
     * Test untuk update data CPL gagal karena validasi.
     */
    public function test_update_data_cpl_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPL');

        $prodi = Prodi::first();

        // Buat data CPL awal menggunakan factory.
        $cpl = CPL::factory()->create([
            'kode_cpl'      => 'k_cpl111',
            'nama_cpl'     => 'Update gagal CPL',
            'deskripsi'     => 'Deskripsi Update gagal CPL',
            'prodi_id' => $prodi->prodi_id
        ]);

        // Buat payload update dengan data yang tidak valid
        $payload = [
            'action'      => 'update',
            'cpl_id'    => $cpl->cpl_id,
            'kode_cpl'  => '',         // kosong agar gagal validasi
            'nama_cpl'  => '',         // kosong agar gagal validasi
            'deskripsi'  => '',         // kosong agar gagal validasi
            'prodi_id' => 9999,       // misalnya, id prodi tidak ada
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpl', $payload);

        // Harapkan status 422 dan validasi error untuk ketiga field
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_cpl', 'nama_cpl', 'prodi_id']);
    }

    /**
     * Test untuk menghapus data CPL.
     */
    public function test_delete_data_cpl_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data CPL');

        $prodi = Prodi::first();
        $cpl = CPL::factory()->create([
            'kode_cpl'      => 'k_cpl222',
            'nama_cpl'     => 'Delete CPL',
            'deskripsi'     => 'Deskripsi Delete CPL',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'delete',
            'cpl_id'     => $cpl->cpl_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data CPL berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('cpl', [
            'cpl_id' => $cpl->cpl_id,
        ]);
    }
}
