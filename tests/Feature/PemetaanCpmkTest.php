<?php

namespace Tests\Feature;

use App\Models\CPL;
use App\Models\CPMK;
use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PemetaanCpmkTest extends TestCase
{
    use RefreshDatabase;

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
     * Test untuk melihat seluruh pemetaan CPMK untuk mata kuliah tertentu.
     */
    public function test_view_all_pemetaan_cpmk(): void
    {
        // Setup role & permission untuk user
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPMK', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Melakukan pemetaan CPMK');

        // Buat data CPL untuk mata kuliah (mapping CPL harus ada terlebih dahulu)
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL100',
            'nama_cpl' => 'CPL 1',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = CPL::factory()->create([
            'kode_cpl' => 'CPL200',
            'nama_cpl' => 'CPL 2',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);

        // Buat mapping CPL untuk mata kuliah (total bobot harus 100%)
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 40.00],
            $cpl2->cpl_id => ['bobot' => 60.00],
        ]);

        // Buat beberapa data CPMK dan mapping-nya via pivot
        $cpmk1 = CPMK::factory()->create(['deskripsi' => 'CPMK 1']);
        $cpmk2 = CPMK::factory()->create(['deskripsi' => 'CPMK 2']);
        $cpmk3 = CPMK::factory()->create(['deskripsi' => 'CPMK 3']);

        // Siapkan data sinkronisasi pivot: key berdasarkan cpmk_id
        // Dalam contoh, kita petakan semua CPMK ke CPL1 dengan bobot sesuai,
        // supaya total bobot misalnya pada CPL1 tidak melebihi 40.
        $syncData = [
            $cpmk1->cpmk_id => ['cpl_id' => $cpl1->cpl_id, 'bobot' => 15.00],
            $cpmk2->cpmk_id => ['cpl_id' => $cpl1->cpl_id, 'bobot' => 25.00],
            $cpmk3->cpmk_id => ['cpl_id' => $cpl2->cpl_id, 'bobot' => 60.00],
        ];

        // Lakukan sync mapping CPMK
        $this->mataKuliah->cpmks()->sync($syncData);

        // Payload untuk melihat seluruh mapping CPMK
        $payload = [
            'action' => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPMK berhasil diambil.'
            ]);

        $this->assertIsArray($response->json('data'));
    }

    /**
     * Test untuk melihat detail pemetaan CPMK berdasarkan ID CPMK.
     * Misal, endpoint mendukung filter dengan menyediakan parameter 'cpmk_id'.
     */
    public function test_view_pemetaan_cpmk_berdasarkan_id(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPMK', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Melakukan pemetaan CPMK');

        // Siapkan mapping CPL terlebih dahulu
        $cpl = CPL::factory()->create([
            'kode_cpl' => 'CPL300',
            'nama_cpl' => 'CPL Detail',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl->cpl_id => ['bobot' => 100.00],
        ]);

        // Buat satu data CPMK dan mapping-nya
        $cpmk = CPMK::factory()->create(['deskripsi' => 'CPMK Detail']);
        $this->mataKuliah->cpmks()->sync([
            $cpmk->cpmk_id => ['cpl_id' => $cpl->cpl_id, 'bobot' => 100.00],
        ]);

        $payload = [
            'action' => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmk_id' => $cpmk->cpmk_id // parameter untuk filter detail mapping
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPMK berhasil diambil.'
            ]);

        $responseData = $response->json('data');
        // Asumsikan API mengembalikan detail mapping sebagai objek (atau array dengan 1 elemen)
        $this->assertEquals($cpmk->cpmk_id, $responseData['cpmk_id'] ?? $responseData[0]['cpmk_id']);
    }

    /**
     * Test untuk store pemetaan CPMK yang berhasil.
     */
    public function test_store_pemetaan_cpmk_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPMK', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Melakukan pemetaan CPMK');

        // Pastikan mapping CPL sudah ada untuk mata kuliah
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL400',
            'nama_cpl' => 'CPL untuk CPMK',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 40.00],
        ]);

        // Buat data CPMK
        $cpmk1 = CPMK::factory()->create(['deskripsi' => 'CPMK Store 1']);
        $cpmk2 = CPMK::factory()->create(['deskripsi' => 'CPMK Store 2']);

        // Payload untuk store pemetaan CPMK; total bobot per CPL harus tidak melebihi 40%
        $payload = [
            'action' => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks' => [
                ['cpmk_id' => $cpmk1->cpmk_id, 'cpl_id' => $cpl1->cpl_id, 'bobot' => 20.00],
                ['cpmk_id' => $cpmk2->cpmk_id, 'cpl_id' => $cpl1->cpl_id, 'bobot' => 20.00],
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpmk', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pemetaan CPMK berhasil ditambahkan.'
            ]);

        // Cek pivot table: masing-masing record harus ada pada cpmk_mata_kuliah
        foreach ($payload['cpmks'] as $item) {
            $this->assertDatabaseHas('cpmk_mata_kuliah', [
                'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
                'cpmk_id'        => $item['cpmk_id'],
                'cpl_id'         => $item['cpl_id'],
                'bobot'          => $item['bobot'],
            ]);
        }
    }

    /**
     * Test untuk store pemetaan CPMK yang gagal karena total bobot melebihi batas.
     * Misalnya, jika total bobot untuk CPL tertentu melebihi bobot CPL yang telah diterapkan.
     */
    public function test_store_pemetaan_cpmk_gagal(): void
    {
        // Siapkan role, permission, dan user untuk testing
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPMK', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Melakukan pemetaan CPMK');

        // Buat mapping CPL dengan bobot tertentu (misal CPL dengan bobot 30)
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL500',
            'nama_cpl' => 'CPL untuk CPMK gagal',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 30.00],
        ]);

        // Buat data CPMK
        $cpmk1 = CPMK::factory()->create(['deskripsi' => 'CPMK Gagal 1']);
        $cpmk2 = CPMK::factory()->create(['deskripsi' => 'CPMK Gagal 2']);

        // Payload gagal: total bobot untuk CPL melebihi yang ditetapkan, misalnya 30 + 10 = 40
        $payload = [
            'action'         => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks'          => [
                ['cpmk_id' => $cpmk1->cpmk_id, 'cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpmk_id' => $cpmk2->cpmk_id, 'cpl_id' => $cpl1->cpl_id, 'bobot' => 10.00],
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpmk', $payload);

        // Perbarui assertion pesan agar sesuai dengan update kode (gunakan "tidak sama" daripada "melebihi")
        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "Total bobot CPMK untuk CPL {$cpl1->cpl_id} tidak sama dengan bobot CPL yang ditetapkan."
            ]);
    }

    /**
     * Test untuk update pemetaan CPMK yang berhasil.
     */
    public function test_update_pemetaan_cpmk_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPMK', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Melakukan pemetaan CPMK');

        // Buat mapping CPL terlebih dahulu
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL600',
            'nama_cpl' => 'CPL untuk update CPMK',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 50.00],
        ]);

        // Buat mapping CPMK awal
        $cpmk1 = CPMK::factory()->create(['deskripsi' => 'CPMK Update 1']);
        $cpmk2 = CPMK::factory()->create(['deskripsi' => 'CPMK Update 2']);
        $this->mataKuliah->cpmks()->sync([
            $cpmk1->cpmk_id => ['cpl_id' => $cpl1->cpl_id, 'bobot' => 50.00],
        ]);

        // Payload update: misalnya ubah bobot menjadi 30 (valid, karena masih ≤ 50)
        $payload = [
            'action' => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks' => [
                ['cpmk_id' => $cpmk1->cpmk_id, 'cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpmk_id' => $cpmk2->cpmk_id, 'cpl_id' => $cpl1->cpl_id, 'bobot' => 20.00],
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pemetaan CPMK berhasil diperbarui.'
            ]);

        // Pastikan perubahan tercermin di database
        $this->assertDatabaseHas('cpmk_mata_kuliah', [
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmk_id' => $cpmk1->cpmk_id,
            'cpl_id'  => $cpl1->cpl_id,
            'bobot'   => 30.00,
        ]);
    }

    /**
     * Test untuk update pemetaan CPMK yang gagal karena validasi.
     * Misalnya, payload update dengan total bobot tidak valid (misalnya, total bobot < batas yang diperlukan).
     */
    public function test_update_pemetaan_cpmk_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPMK', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Melakukan pemetaan CPMK');

        // Buat mapping CPL terlebih dahulu dengan bobot 50
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL700',
            'nama_cpl' => 'CPL untuk update gagal CPMK',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 50.00],
        ]);

        // Buat mapping CPMK awal
        $cpmk1 = CPMK::factory()->create(['deskripsi' => 'CPMK Update Gagal']);
        $this->mataKuliah->cpmks()->sync([
            $cpmk1->cpmk_id => ['cpl_id' => $cpl1->cpl_id, 'bobot' => 50.00],
        ]);

        // Payload update gagal: ubah bobot sehingga total bobot menjadi 30 (tidak sama dengan bobot CPL yang ditetapkan)
        $payload = [
            'action' => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks' => [
                ['cpmk_id' => $cpmk1->cpmk_id, 'cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpmk', $payload);
        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "Total bobot CPMK untuk CPL {$cpl1->cpl_id} tidak sama dengan bobot CPL yang ditetapkan."
            ]);
    }

    /**
     * Test untuk menghapus pemetaan CPMK.
     */
    public function test_delete_pemetaan_cpmk_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPMK', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Melakukan pemetaan CPMK');

        // Buat mapping CPMK awal:
        // Misalnya, buat satu CPL dengan bobot 100 dan satu CPMK yang ter-mapping ke CPL tersebut.
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL800',
            'nama_cpl' => 'CPL Delete',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 100.00],
        ]);

        $cpmk1 = CPMK::factory()->create(['deskripsi' => 'CPMK Delete']);
        $this->mataKuliah->cpmks()->sync([
            $cpmk1->cpmk_id => ['cpl_id' => $cpl1->cpl_id, 'bobot' => 100.00],
        ]);

        // Karena aturan validasi (di PemetaanCpmkRequest) mengharuskan key 'cpmks' ada untuk aksi store/update
        // (dan sekarang juga untuk aksi delete agar payload tidak terlewatkan),
        // sertakan data cpmks dalam payload delete agar validasi terpenuhi.
        $payload = [
            'action'         => 'delete',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks'          => [
                [
                    'cpmk_id' => $cpmk1->cpmk_id,
                    'cpl_id'  => $cpl1->cpl_id,
                ]
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/pemetaan-cpmk', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pemetaan CPMK berhasil dihapus.'
            ]);

        // Pastikan bahwa pivot dengan kombinasi data tersebut sudah tidak ada
        $this->assertDatabaseMissing('cpmk_mata_kuliah', [
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmk_id'        => $cpmk1->cpmk_id,
            'cpl_id'         => $cpl1->cpl_id,
        ]);
    }
}
