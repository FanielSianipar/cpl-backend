<?php

namespace Tests\Feature;

use App\Models\CPL;
use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PemetaanCplTest extends TestCase
{
    use DatabaseTransactions;

    protected $mataKuliah;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pastikan ada data fakultas dan prodi untuk operasi pemetaan CPL
        $fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK100',
            'nama_fakultas' => 'Pertambangan',
        ]);
        $prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD100',
            'nama_prodi'  => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        // Pastikan ada satu data mata kuliah untuk operasi mapping
        $this->mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK100',
            'nama_mata_kuliah' => 'Matematika Dasar',
            'prodi_id'         => $prodi->prodi_id,
        ]);
    }

    /**
     * Test untuk melihat seluruh mapping CPL untuk suatu mata kuliah.
     * (Misalnya, jika pada sebuah mata kuliah sudah ada beberapa mapping,
     * endpoint view tanpa menyertakan `cpl_id` akan mengembalikan seluruh data mapping.)
     */
    public function test_view_all_pemetaan_cpl(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola pemetaan CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola pemetaan CPL');

        // Buat beberapa mapping CPL untuk mata kuliah
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL100',
            'nama_cpl' => 'CPL 1',
            'prodi_id' => $this->mataKuliah->prodi_id
        ]);
        $cpl2 = CPL::factory()->create([
            'kode_cpl' => 'CPL200',
            'nama_cpl' => 'CPL 2',
            'prodi_id' => $this->mataKuliah->prodi_id
        ]);
        $cpl3 = CPL::factory()->create([
            'kode_cpl' => 'CPL300',
            'nama_cpl' => 'CPL 3',
            'prodi_id' => $this->mataKuliah->prodi_id
        ]);

        // Sync mapping: Total bobot 100%
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 30.00],
            $cpl2->cpl_id => ['bobot' => 30.00],
            $cpl3->cpl_id => ['bobot' => 40.00],
        ]);

        $payload = [
            'action' => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id // untuk melihat semua mapping di mata kuliah ini
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPL berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(3, count($responseData));
    }

    /**
     * Test untuk melihat detail mapping CPL berdasarkan ID CPL (dengan menyediakan parameter cpl_id di request).
     * Asumsikan endpoint memeriksa keberadaan key 'cpl_id' untuk menampilkan detail satu mapping.
     */
    public function test_view_pemetaan_cpl_berdasarkan_id(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola pemetaan CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola pemetaan CPL');

        // Buat mapping CPL
        $cpl = CPL::factory()->create([
            'kode_cpl' => 'CPL400',
            'nama_cpl' => 'CPL Detail',
            'prodi_id' => $this->mataKuliah->prodi_id
        ]);

        // Sync satu mapping saja (untuk keperluan detail)
        $this->mataKuliah->cpls()->sync([
            $cpl->cpl_id => ['bobot' => 100.00],
        ]);

        $payload = [
            'action' => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpl_id' => $cpl->cpl_id  // misalnya, jika endpoint mendukung filter detail mapping berdasarkan cpl_id
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPL berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        // Asumsikan jika cpl_id disediakan, API mengembalikan data mapping tunggal
        $this->assertEquals($cpl->cpl_id, $responseData['cpl_id'] ?? $responseData[0]['cpl_id']);
    }

    /**
     * Test untuk menyimpan (store) pemetaan CPL baru.
     */
    public function test_store_pemetaan_cpl_berhasil(): void
    {
        // Buat role dan permission
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola pemetaan CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola pemetaan CPL');

        // Persiapkan data CPL untuk mapping
        $cpl1 = CPL::factory()->create(['kode_cpl' => 'CPL101', 'nama_cpl' => 'CPL 1', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $cpl2 = CPL::factory()->create(['kode_cpl' => 'CPL102', 'nama_cpl' => 'CPL 2', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $cpl3 = CPL::factory()->create(['kode_cpl' => 'CPL103', 'nama_cpl' => 'CPL 3', 'prodi_id' => $this->mataKuliah->prodi_id]);

        $payload = [
            'action' => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls' => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 40.00],
                ['cpl_id' => $cpl3->cpl_id, 'bobot' => 30.00],
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpl', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pemetaan CPL berhasil ditambahkan.'
            ]);

        // Pastikan data di pivot tersimpan (pada table cpl_mata_kuliah)
        foreach ($payload['cpls'] as $item) {
            $this->assertDatabaseHas('cpl_mata_kuliah', [
                'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
                'cpl_id'         => $item['cpl_id'],
                'bobot'          => $item['bobot'],
            ]);
        }
    }

    /**
     * Test untuk store pemetaan CPL dengan data yang tidak valid sehingga gagal.
     * Misalnya, total bobot tidak mencapai 100% atau field tidak lengkap.
     */
    public function test_store_pemetaan_cpl_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola pemetaan CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola pemetaan CPL');

        // Buat beberapa data CPL
        $cpl1 = CPL::factory()->create(['kode_cpl' => 'CPL501', 'nama_cpl' => 'CPL Gagal 1', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $cpl2 = CPL::factory()->create(['kode_cpl' => 'CPL502', 'nama_cpl' => 'CPL Gagal 2', 'prodi_id' => $this->mataKuliah->prodi_id]);

        // Payload gagal: total bobot hanya 50% (harus 100%)
        $payload = [
            'action' => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls' => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 20.00]
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpl', $payload);
        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Total bobot CPL harus 100%'
            ]);
    }

    /**
     * Test untuk mengupdate pemetaan CPL.
     */
    public function test_update_pemetaan_cpl_berhasil(): void
    {
        // Buat role dan permission
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola pemetaan CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola pemetaan CPL');

        // Pertama, buat mapping awal
        $cpl1 = CPL::factory()->create(['kode_cpl' => 'CPL201', 'nama_cpl' => 'CPL 1', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $cpl2 = CPL::factory()->create(['kode_cpl' => 'CPL202', 'nama_cpl' => 'CPL 2', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 50.00],
            $cpl2->cpl_id => ['bobot' => 50.00],
        ]);

        // Payload update: misalnya mengganti bobot CPL 1 menjadi 30 dan CPL 2 menjadi 70
        $payload = [
            'action' => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls' => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 70.00],
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pemetaan CPL berhasil diperbarui.'
            ]);

        // Cek perubahan di database
        $this->assertDatabaseHas('cpl_mata_kuliah', [
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpl_id'         => $cpl1->cpl_id,
            'bobot'          => 30.00,
        ]);
        $this->assertDatabaseHas('cpl_mata_kuliah', [
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpl_id'         => $cpl2->cpl_id,
            'bobot'          => 70.00,
        ]);
    }

    /**
     * Test untuk update pemetaan CPL yang gagal karena validasi.
     * Misalnya, ketika total bobot tidak mencapai 100% atau
     * jika mapping tidak ada untuk update.
     */
    public function test_update_pemetaan_cpl_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola pemetaan CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola pemetaan CPL');

        // Pertama, buat mapping awal
        $cpl1 = CPL::factory()->create(['kode_cpl' => 'CPL601', 'nama_cpl' => 'CPL Update Gagal 1', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $cpl2 = CPL::factory()->create(['kode_cpl' => 'CPL602', 'nama_cpl' => 'CPL Update Gagal 2', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 50.00],
            $cpl2->cpl_id => ['bobot' => 50.00],
        ]);

        // Payload update gagal: total bobot menjadi 80% (bukan 100%)
        $payload = [
            'action' => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls' => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 50.00],
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpl', $payload);
        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Total bobot CPL harus 100%'
            ]);
    }

    /**
     * Test untuk menghapus pemetaan CPL.
     */
    public function test_delete_pemetaan_cpl_berhasil(): void
    {
        // Buat role dan permission
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola pemetaan CPL', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola pemetaan CPL');

        // Pertama, buat mapping awal untuk mata kuliah
        $cpl1 = CPL::factory()->create(['kode_cpl' => 'CPL301', 'nama_cpl' => 'CPL 1', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $cpl2 = CPL::factory()->create(['kode_cpl' => 'CPL302', 'nama_cpl' => 'CPL 2', 'prodi_id' => $this->mataKuliah->prodi_id]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 40.00],
            $cpl2->cpl_id => ['bobot' => 60.00],
        ]);

        $payload = [
            'action' => 'delete',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpl', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pemetaan CPL berhasil dihapus.'
            ]);

        // Pastikan mapping sudah terhapus di database
        $this->assertDatabaseMissing('cpl_mata_kuliah', [
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
    }
}
