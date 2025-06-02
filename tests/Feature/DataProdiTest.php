<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataProdiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada satu data Fakultas untuk semua operasi prodi
        Fakultas::factory()->create([
            'kode_fakultas' => 'FK01',
            'nama_fakultas' => 'Fakultas Teknik',
        ]);
    }

    /**
     * Test untuk menampilkan seluruh data Prodi.
     */
    public function test_view_all_data_prodi(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data prodi');

        $fakultas = Fakultas::first();

        // Buat beberapa data Prodi.
        Prodi::factory()->create([
            'kode_prodi'  => 'PRD001',
            'nama_prodi'  => 'Teknik Informatika',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);
        Prodi::factory()->create([
            'kode_prodi'  => 'PRD002',
            'nama_prodi'  => 'Sistem Informasi',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        $payload = [
            'action' => 'view'
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-prodi', $payload);
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
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data prodi');

        $fakultas = Fakultas::first();
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD003',
            'nama_prodi'  => 'Teknik Elektro',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        $payload = [
            'action' => 'view',
            'prodi_id'     => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-prodi', $payload);
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
        $fakultas = Fakultas::first();

        // Buat role dan permission untuk user yang melakukan operasi
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data prodi');

        $payload = [
            'action'      => 'store',
            'kode_prodi'  => 'PRD001000',
            'nama_prodi'  => 'Teknik Informatika',
            'fakultas_id' => $fakultas->fakultas_id,
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-prodi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data prodi berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('prodi', [
            'kode_prodi'  => 'PRD001',
            'nama_prodi'  => 'Teknik Informatika',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);
    }

    /**
     * Test untuk store data prodi gagal karena validasi.
     */
    public function test_store_data_prodi_validasi_gagal(): void
    {
        // Atur role dan permission untuk user yang melakukan operasi.
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data prodi');

        // Buat payload dengan data yang tidak valid: kosong atau tidak sesuai
        $payload = [
            'action'      => 'store',
            'kode_prodi'  => '',   // kosong, sehingga gagal validasi
            'nama_prodi'  => '',   // kosong, sehingga gagal validasi
            'fakultas_id' => '',   // tidak valid, wajib angka dan harus ada di tabel fakultas
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-prodi', $payload);

        // Harapkan status 422 beserta error validasi untuk field terkait
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_prodi', 'nama_prodi', 'fakultas_id']);
    }

    /**
     * Test untuk update data Prodi.
     */
    public function test_update_data_prodi_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data prodi');

        $fakultas = Fakultas::first();
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD004',
            'nama_prodi'  => 'Teknik Mesin',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        $payload = [
            'action'      => 'update',
            'prodi_id'    => $prodi->prodi_id,
            'kode_prodi'  => 'PRD004', // kode yang sama atau bisa diubah
            'nama_prodi'  => 'Teknik Mesin Terbaru',
            'fakultas_id' => $fakultas->fakultas_id,
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-prodi', $payload);
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
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data prodi');

        $fakultas = Fakultas::first();

        // Buat data prodi awal menggunakan factory.
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD006',
            'nama_prodi'  => 'Teknik Sipil',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        // Buat payload update dengan data yang tidak valid
        $payload = [
            'action'      => 'update',
            'prodi_id'    => $prodi->prodi_id,
            'kode_prodi'  => '',         // kosong agar gagal validasi
            'nama_prodi'  => '',         // kosong agar gagal validasi
            'fakultas_id' => 9999,       // misalnya, id fakultas tidak ada
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-prodi', $payload);

        // Harapkan status 422 dan validasi error untuk ketiga field
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode_prodi', 'nama_prodi', 'fakultas_id']);
    }

    /**
     * Test untuk menghapus data Prodi.
     */
    public function test_delete_data_prodi_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data prodi');

        $fakultas = Fakultas::first();
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD005',
            'nama_prodi'  => 'Teknik Industri',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        $payload = [
            'action' => 'delete',
            'prodi_id'     => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-prodi', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data prodi berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('prodi', [
            'prodi_id' => $prodi->prodi_id,
        ]);
    }
}
