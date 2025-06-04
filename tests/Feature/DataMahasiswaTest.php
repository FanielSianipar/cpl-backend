<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataMahasiswaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada satu data Fakultas dan Prodi untuk semua operasi mahasiswa
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
     * Test untuk menampilkan seluruh data Mahasiswa.
     */
    public function test_view_all_data_mahasiswa(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mahasiswa');

        $prodi = Prodi::first();

        // Buat beberapa data Mahasiswa.
        Mahasiswa::factory()->create([
            'npm'      => '201506444',
            'name'     => 'Nama1 Mahasiswa',
            'email'    => 'nama1mahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ]);
        Mahasiswa::factory()->create([
            'npm'      => '201506555',
            'name'     => 'Nama2 Mahasiswa',
            'email'    => 'nama2mahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view'
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mahasiswa', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data mahasiswa berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    /**
     * Test untuk mengambil detail satu data Mahasiswa berdasarkan ID.
     */
    public function test_view_detail_data_mahasiswa_berdasarkan_id(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mahasiswa');

        $prodi = Prodi::first();
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506999',
            'name'     => 'Nama Mahasiswa',
            'email'    => 'namamahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'view',
            'mahasiswa_id'     => $mahasiswa->mahasiswa_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mahasiswa', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertEquals($mahasiswa->mahasiswa_id, $responseData['mahasiswa_id']);
        $this->assertEquals($mahasiswa->email, $responseData['email']);
    }

    /**
     * Test untuk menyimpan data Mahasiswa baru.
     */
    public function test_store_data_mahasiswa_berhasil(): void
    {
        $prodi = Prodi::first();

        // Buat role dan permission untuk user yang melakukan operasi
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mahasiswa');

        $payload = [
            'action'      => 'store',
            'npm'      => '201506999',
            'name'     => 'Store Mahasiswa',
            'email'    => 'storemahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mahasiswa', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil dibuat.'
            ]);

        $this->assertDatabaseHas('mahasiswa', [
            'npm'  => '201506999',
            'email'    => 'storemahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id,
        ]);
    }

    /**
     * Test untuk store data mahasiswa gagal karena validasi.
     */
    public function test_store_data_mahasiswa_validasi_gagal(): void
    {
        // Atur role dan permission untuk user yang melakukan operasi.
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mahasiswa');

        // Buat payload dengan data yang tidak valid: kosong atau tidak sesuai
        $payload = [
            'action'      => 'store',
            'npm'  => '',   // kosong, sehingga gagal validasi
            'name'  => '',   // kosong, sehingga gagal validasi
            'email'  => 'invalid-email',   // format email tidak valid
            'prodi_id' => '',   // kosong, sehingga gagal validasi
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mahasiswa', $payload);

        // Harapkan status 422 beserta error validasi untuk field terkait
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['npm', 'name', 'email', 'prodi_id']);
    }

    /**
     * Test untuk update data Mahasiswa.
     */
    public function test_update_data_mahasiswa_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mahasiswa');

        $prodi = Prodi::first();
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506000',
            'name'     => 'Update Mahasiswa',
            'email'    => 'updatemahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action'      => 'update',
            'mahasiswa_id'    => $mahasiswa->mahasiswa_id,
            'npm'      => '201506888',
            'name'     => 'Updated Mahasiswa',
            'email'    => 'updatedmahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mahasiswa', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil diperbarui.'
            ]);

        $this->assertDatabaseHas('mahasiswa', [
            'mahasiswa_id'   => $mahasiswa->mahasiswa_id,
            'npm'      => '201506888',
            'name'     => 'Updated Mahasiswa',
            'email'    => 'updatedmahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ]);
    }

    /**
     * Test untuk update data mahasiswa gagal karena validasi.
     */
    public function test_update_data_mahasiswa_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mahasiswa');

        $prodi = Prodi::first();

        // Buat data mahasiswa awal menggunakan factory.
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506111',
            'name'     => 'Update gagal Mahasiswa',
            'email'    => 'updategagalmahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ]);

        // Buat payload update dengan data yang tidak valid
        $payload = [
            'action'      => 'update',
            'mahasiswa_id'    => $mahasiswa->mahasiswa_id,
            'npm'  => '',         // kosong agar gagal validasi
            'name'  => '',         // kosong agar gagal validasi
            'email'  => 'invalid-email', // format email tidak valid
            'prodi_id' => 9999,       // misalnya, id prodi tidak ada
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mahasiswa', $payload);

        // Harapkan status 422 dan validasi error untuk ketiga field
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['npm', 'name', 'email', 'prodi_id']);
    }

    /**
     * Test untuk menghapus data Mahasiswa.
     */
    public function test_delete_data_mahasiswa_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola data mahasiswa', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola data mahasiswa');

        $prodi = Prodi::first();
        $mahasiswa = Mahasiswa::factory()->create([
            'npm'      => '201506222',
            'name'     => 'Delete Mahasiswa',
            'email'    => 'deletemahasiswa@example.com',
            'prodi_id' => $prodi->prodi_id
        ]);

        $payload = [
            'action' => 'delete',
            'mahasiswa_id'     => $mahasiswa->mahasiswa_id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-data-mahasiswa', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data mahasiswa berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('mahasiswa', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ]);
    }
}
