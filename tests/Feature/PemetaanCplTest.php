<?php

namespace Tests\Feature;

use App\Models\CPL;
use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PemetaanCplTest extends TestCase
{
    use RefreshDatabase;

    protected $prodi;
    protected $adminProdiRole;
    protected $permission;
    protected $user;
    protected $mataKuliah;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat data Fakultas
        $fakultas = Fakultas::factory()->create([
            'kode_fakultas' => 'FK100',
            'nama_fakultas' => 'Pertambangan',
        ]);

        $this->prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD100',
            'nama_prodi'  => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        // Buat role Admin Prodi dengan izin mengelola CPL
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Melakukan pemetaan CPL', 'guard_name' => 'web']);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create([
            'prodi_id' => $this->prodi->prodi_id
        ]);
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);

        // Buat satu data Mata Kuliah untuk operasi mapping CPL
        $this->mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK100',
            'nama_mata_kuliah' => 'Matematika Dasar',
            'prodi_id'         => $this->prodi->prodi_id,
        ]);
    }

    /**
     * Test untuk melihat seluruh mapping CPL pada suatu mata kuliah.
     */
    public function test_view_all_pemetaan_cpl(): void
    {
        // Buat beberapa data CPL
        $cpl1 = Cpl::factory()->create([
            'kode_cpl' => 'CPL100',
            'nama_cpl' => 'CPL 1',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = Cpl::factory()->create([
            'kode_cpl' => 'CPL200',
            'nama_cpl' => 'CPL 2',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl3 = Cpl::factory()->create([
            'kode_cpl' => 'CPL300',
            'nama_cpl' => 'CPL 3',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);

        // Mapping CPL ke mata kuliah (total bobot 100%)
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 30.00],
            $cpl2->cpl_id => ['bobot' => 30.00],
            $cpl3->cpl_id => ['bobot' => 40.00],
        ]);

        $payload = [
            'action'         => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpl', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPL berhasil diambil.'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(3, count($data));
    }

    /**
     * Test untuk melihat detail mapping CPL berdasarkan cpl_id.
     */
    public function test_view_pemetaan_cpl_berdasarkan_id(): void
    {
        $cpl = Cpl::factory()->create([
            'kode_cpl' => 'CPL400',
            'nama_cpl' => 'CPL Detail',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);

        // Sync hanya satu mapping untuk detail tampilan
        $this->mataKuliah->cpls()->sync([
            $cpl->cpl_id => ['bobot' => 100.00],
        ]);

        $payload = [
            'action'         => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpl_id'         => $cpl->cpl_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpl', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPL berhasil diambil.',
            ]);

        $data = $response->json('data');
        $returnedCplId = isset($data['cpl_id']) ? $data['cpl_id'] : $data[0]['cpl_id'];
        $this->assertEquals($cpl->cpl_id, $returnedCplId);
    }

    /**
     * Test untuk menyimpan pemetaan CPL baru yang berhasil.
     */
    public function test_store_pemetaan_cpl_berhasil(): void
    {
        // Persiapkan data CPL untuk mapping
        $cpl1 = Cpl::factory()->create([
            'kode_cpl' => 'CPL101',
            'nama_cpl' => 'CPL 1',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = Cpl::factory()->create([
            'kode_cpl' => 'CPL102',
            'nama_cpl' => 'CPL 2',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl3 = Cpl::factory()->create([
            'kode_cpl' => 'CPL103',
            'nama_cpl' => 'CPL 3',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);

        $payload = [
            'action'         => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls'           => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 40.00],
                ['cpl_id' => $cpl3->cpl_id, 'bobot' => 30.00],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpl', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pemetaan CPL berhasil ditambahkan.'
            ]);

        foreach ($payload['cpls'] as $item) {
            $this->assertDatabaseHas('cpl_mata_kuliah', [
                'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
                'cpl_id'         => $item['cpl_id'],
                'bobot'          => $item['bobot'],
            ]);
        }
    }

    /**
     * Test untuk penyimpanan pemetaan CPL yang gagal karena total bobot tidak mencapai 100%.
     */
    public function test_store_pemetaan_cpl_gagal(): void
    {
        $cpl1 = Cpl::factory()->create([
            'kode_cpl' => 'CPL501',
            'nama_cpl' => 'CPL Gagal 1',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = Cpl::factory()->create([
            'kode_cpl' => 'CPL502',
            'nama_cpl' => 'CPL Gagal 2',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);

        // Total bobot hanya 50%
        $payload = [
            'action'         => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls'           => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 20.00],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpl', $payload);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Total bobot CPL harus 100%'
            ]);
    }

    /**
     * Test untuk update pemetaan CPL yang berhasil.
     */
    public function test_update_pemetaan_cpl_berhasil(): void
    {
        $cpl1 = Cpl::factory()->create([
            'kode_cpl' => 'CPL201',
            'nama_cpl' => 'CPL 1',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = Cpl::factory()->create([
            'kode_cpl' => 'CPL202',
            'nama_cpl' => 'CPL 2',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 50.00],
            $cpl2->cpl_id => ['bobot' => 50.00],
        ]);

        // Update: ubah bobot CPL 1 menjadi 30, CPL 2 menjadi 70
        $payload = [
            'action'         => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls'           => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 70.00],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpl', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pemetaan CPL berhasil diperbarui.'
            ]);

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
     * Test untuk update pemetaan CPL yang gagal karena validasi (total bobot tidak 100%).
     */
    public function test_update_pemetaan_cpl_gagal(): void
    {
        $cpl1 = Cpl::factory()->create([
            'kode_cpl' => 'CPL601',
            'nama_cpl' => 'CPL Update Gagal 1',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = Cpl::factory()->create([
            'kode_cpl' => 'CPL602',
            'nama_cpl' => 'CPL Update Gagal 2',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 50.00],
            $cpl2->cpl_id => ['bobot' => 50.00],
        ]);

        // Update gagal: total bobot menjadi 80% (harus 100%)
        $payload = [
            'action'         => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpls'           => [
                ['cpl_id' => $cpl1->cpl_id, 'bobot' => 30.00],
                ['cpl_id' => $cpl2->cpl_id, 'bobot' => 50.00],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpl', $payload);

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
        $cpl1 = Cpl::factory()->create([
            'kode_cpl' => 'CPL301',
            'nama_cpl' => 'CPL 1',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = Cpl::factory()->create([
            'kode_cpl' => 'CPL302',
            'nama_cpl' => 'CPL 2',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 40.00],
            $cpl2->cpl_id => ['bobot' => 60.00],
        ]);

        $payload = [
            'action'         => 'delete',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpl', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pemetaan CPL berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('cpl_mata_kuliah', [
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
    }
}
