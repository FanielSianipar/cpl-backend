<?php

namespace Tests\Feature;

use App\Models\CPL;
use App\Models\CPMK;
use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\MataKuliahCpmkPivot;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PemetaanCpmkTest extends TestCase
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

        // Buat data Prodi sesuai dengan format yang pernah Anda berikan
        $this->prodi = Prodi::factory()->create([
            'kode_prodi'  => 'PRD100',
            'nama_prodi'  => 'Pertambangan Batu Bara',
            'fakultas_id' => $fakultas->fakultas_id,
        ]);

        // Buat role Admin Prodi dengan izin untuk pemetaan CPMK
        $this->adminProdiRole = Role::firstOrCreate([
            'name'       => 'Admin Prodi',
            'guard_name' => 'web',
        ]);
        $this->permission = Permission::firstOrCreate([
            'name'       => 'Melakukan pemetaan CPMK',
            'guard_name' => 'web',
        ]);

        // Buat user acting (Admin Prodi) dan berikan role serta permission
        $this->user = User::factory()->create([
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $this->user->assignRole($this->adminProdiRole);
        $this->adminProdiRole->givePermissionTo($this->permission);

        // Buat satu data Mata Kuliah untuk operasi mapping CPMK
        $this->mataKuliah = MataKuliah::factory()->create([
            'kode_mata_kuliah' => 'MK100',
            'nama_mata_kuliah' => 'Matematika Dasar',
            'prodi_id'         => $this->prodi->prodi_id,
        ]);

        // Pastikan Mata Kuliah memiliki CPL yang valid
        $cpl1 = CPL::factory()->create([
            'kode_cpl' => 'CPL100',
            'deskripsi' => 'CPL untuk Matematika Dasar',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $cpl2 = CPL::factory()->create([
            'kode_cpl' => 'CPL200',
            'deskripsi' => 'CPL untuk Matematika Lanjutan',
            'prodi_id' => $this->mataKuliah->prodi_id,
        ]);
        $this->mataKuliah->cpls()->sync([
            $cpl1->cpl_id => ['bobot' => 50.00],
            $cpl2->cpl_id => ['bobot' => 50.00],
        ]);
    }

    /**
     * Test untuk melihat seluruh pemetaan CPMK untuk mata kuliah tertentu.
     */
    public function test_view_all_pemetaan_cpmk(): void
    {
        // Ambil daftar cpl_id yang sudah di‐sync di setUp()
        $cplList = $this->mataKuliah->cpls->pluck('cpl_id')->all();

        // Buat 3 CPMK untuk mata kuliah ini
        $cpmk1 = CPMK::factory()->create([
            'kode_cpmk'      => 'kcpmk1',
            'nama_cpmk'      => 'cpmk1',
            'deskripsi'      => 'deskripsi cpmk1',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
        $cpmk2 = CPMK::factory()->create([
            'kode_cpmk'      => 'kcpmk2',
            'nama_cpmk'      => 'cpmk2',
            'deskripsi'      => 'deskripsi cpmk2',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
        $cpmk3 = CPMK::factory()->create([
            'kode_cpmk'      => 'kcpmk3',
            'nama_cpmk'      => 'cpmk3',
            'deskripsi'      => 'deskripsi cpmk3',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        // Mapping CPMK → CPL via pivot cpmk_mata_kuliah
        // Dua CPMK pertama terkait ke CPL pertama (total 25% + 25% = 50%)
        $cpmk1->cpls()->attach($cplList[0], ['bobot' => 25.00]);
        $cpmk2->cpls()->attach($cplList[0], ['bobot' => 25.00]);

        // CPMK ketiga terkait ke CPL kedua (50%)
        $cpmk3->cpls()->attach($cplList[1], ['bobot' => 50.00]);

        // Panggil endpoint view
        $payload = [
            'action'         => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpmk', $payload);

        // Pastikan status & struktur JSON
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPMK berhasil diambil.'
            ])
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'cpmk_mata_kuliah_id',
                        'mata_kuliah_id',
                        'cpmk_id',
                        'cpl_id',
                        'bobot',
                    ]
                ]
            ]);

        // Pilihan tambahan: cek bahwa setiap mapping benar
        $all = $response->json('data');
        $this->assertEquals(25.00, collect($all)
            ->where('cpmk_id', $cpmk1->cpmk_id)->first()['bobot']);
        $this->assertEquals(50.00, collect($all)
            ->where('cpmk_id', $cpmk3->cpmk_id)->first()['bobot']);
    }

    /**
     * Test untuk melihat detail pemetaan CPMK berdasarkan ID CPMK.
     * Misal, jika parameter 'cpmk_id' disertakan, endpoint mengembalikan detail mapping tunggal.
     */
    public function test_view_pemetaan_cpmk_berdasarkan_id(): void
    {
        // Buat satu data CPMK dan mapping-nya
        $cpmk = CPMK::factory()->create([
            'kode_cpmk' => 'kcpmkD',
            'nama_cpmk' => 'cpmkDetail',
            'deskripsi' => 'CPMK Detail',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
        $syncData = [
            $cpmk->cpmk_id => ['cpl_id' => $this->mataKuliah->cpls()->first()->cpl_id, 'bobot' => 100.00],
        ];
        foreach ($syncData as $cpmkId => $pivot) {
            CPMK::find($cpmkId)
                ->cpls()
                ->attach([$pivot['cpl_id'] => ['bobot' => $pivot['bobot']]]);
        }

        $payload = [
            'action'         => 'view',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmk_id'        => $cpmk->cpmk_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpmk', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data pemetaan CPMK berhasil diambil.',
            ]);

        $data = $response->json('data');
        $returnedCpmkId = isset($data['cpmk_id']) ? $data['cpmk_id'] : $data[0]['cpmk_id'];
        $this->assertEquals($cpmk->cpmk_id, $returnedCpmkId);
    }

    /**
     * Test untuk menyimpan pemetaan CPMK yang berhasil.
     */
    public function test_store_pemetaan_cpmk_berhasil(): void
    {
        // Buat data CPMK
        $cpmk1 = CPMK::factory()->create([
            'kode_cpmk'      => 'kcpmkB',
            'nama_cpmk'      => 'cpmkBerhasil',
            'deskripsi'      => 'deskripsi cpmkBerhasil',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
        $cpmk2 = CPMK::factory()->create([
            'kode_cpmk'      => 'kcpmkB2',
            'nama_cpmk'      => 'cpmkBerhasil2',
            'deskripsi'      => 'deskripsi cpmkBerhasil2',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        $payload = [
            'action'         => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks'          => [
                [
                    'cpmk_id' => $cpmk1->cpmk_id,
                    'cpl_id'  => $this->mataKuliah->cpls()->first()->cpl_id,
                    'bobot'   => 25.00,
                ],
                [
                    'cpmk_id' => $cpmk2->cpmk_id,
                    'cpl_id'  => $this->mataKuliah->cpls()->latest('cpl_id')->first()->cpl_id,
                    'bobot'   => 25.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpmk', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pemetaan CPMK berhasil ditambahkan.'
            ])
            ->assertJsonCount(2, 'data')      // dua mapping
            ->assertJsonStructure([
                'data' => [
                    '*' => [                 // setiap element:
                        'cpmk_mata_kuliah_id',
                        'mata_kuliah_id',
                        'cpmk_id',
                        'cpl_id',
                        'bobot',
                    ]
                ]
            ]);

        // Cek database
        foreach ($payload['cpmks'] as $item) {
            $this->assertDatabaseHas('cpmk_mata_kuliah', [
                'cpmk_id' => $item['cpmk_id'],
                'cpl_id'  => $item['cpl_id'],
                'bobot'   => $item['bobot'],
            ]);
        }
    }

    /**
     * Test untuk store pemetaan CPMK yang gagal karena total bobot melebihi batas.
     * Misalnya, jika total bobot untuk CPL tertentu tidak sesuai dengan bobot CPL yang ditetapkan.
     */
    public function test_store_pemetaan_cpmk_gagal(): void
    {
        // Buat data CPMK
        $cpmk1 = CPMK::factory()->create([
            'kode_cpmk' => 'kcpmkG',
            'nama_cpmk' => 'cpmkGagal',
            'deskripsi' => 'deskripsi cpmkGagal',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id
        ]);
        $cpmk2 = CPMK::factory()->create([
            'kode_cpmk' => 'kcpmkG2',
            'nama_cpmk' => 'cpmkGagal2',
            'deskripsi' => 'deskripsi cpmkGagal2',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id
        ]);

        // Payload gagal: total bobot untuk CPL melebihi yang ditetapkan, misalnya 30 + 10 = 40
        $payload = [
            'action'         => 'store',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks'          => [
                ['cpmk_id' => $cpmk1->cpmk_id, 'cpl_id' => $this->mataKuliah->cpls->first()->cpl_id, 'bobot' => 100.00],
                ['cpmk_id' => $cpmk2->cpmk_id, 'cpl_id' => $this->mataKuliah->cpls->first()->cpl_id, 'bobot' => 25.00],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpmk', $payload);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "Total bobot CPMK untuk CPL {$this->mataKuliah->cpls->first()->cpl_id} melebihi bobot CPL yang ditetapkan."
            ]);
    }

    /**
     * Test untuk update pemetaan CPMK yang berhasil.
     */
    public function test_update_pemetaan_cpmk_berhasil(): void
    {
        // Buat mapping CPMK awal
        $cpmk1 = CPMK::factory()->create([
            'kode_cpmk' => 'kcpmkU',
            'nama_cpmk' => 'cpmkUpdate',
            'deskripsi' => 'deskripsi cpmkUpdate',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id
        ]);
        $cpmk2 = CPMK::factory()->create([
            'kode_cpmk' => 'kcpmkU2',
            'nama_cpmk' => 'cpmkUpdate2',
            'deskripsi' => 'deskripsi Update2',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id
        ]);

        // Payload update: ubah bobot sehingga total CPMK untuk CPL tersebut menjadi 30 + 20 = 50 (valid)
        $payload = [
            'action'         => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks'          => [
                ['cpmk_id' => $cpmk1->cpmk_id, 'cpl_id' => $this->mataKuliah->cpls->first()->cpl_id, 'bobot' => 30.00],
                ['cpmk_id' => $cpmk2->cpmk_id, 'cpl_id' => $this->mataKuliah->cpls->first()->cpl_id, 'bobot' => 20.00],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpmk', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pemetaan CPMK berhasil diperbarui.'
            ]);

        $this->assertDatabaseHas('cpmk_mata_kuliah', [
            'cpmk_id'        => $cpmk1->cpmk_id,
            'cpl_id'         => $this->mataKuliah->cpls->first()->cpl_id,
            'bobot'          => 30.00,
        ]);
    }

    /**
     * Test untuk update pemetaan CPMK yang gagal karena validasi (total bobot tidak sama).
     */
    public function test_update_pemetaan_cpmk_gagal(): void
    {
        // Buat mapping CPMK awal
        $cpmk1 = CPMK::factory()->create([
            'kode_cpmk' => 'kcpmkUG',
            'nama_cpmk' => 'cpmkUpdateGagal',
            'deskripsi' => 'deskripsi cpmkUpdateGagal',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id
        ]);

        // Payload update gagal: total bobot menjadi 60 (melebihi bobot CPL yang ditetapkan)
        $payload = [
            'action'         => 'update',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'cpmks'          => [
                ['cpmk_id' => $cpmk1->cpmk_id, 'cpl_id' => $this->mataKuliah->cpls->first()->cpl_id, 'bobot' => 60.00],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpmk', $payload);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "Total bobot CPMK untuk CPL {$this->mataKuliah->cpls->first()->cpl_id} melebihi bobot CPL yang ditetapkan."
            ]);
    }

    /**
     * Test untuk menghapus pemetaan CPMK.
     */
    public function test_delete_pemetaan_cpmk_berhasil(): void
    {
        // 1. Siapkan CPMK dan buat relasi pivot CPL
        $cpmk = CPMK::factory()->create([
            'kode_cpmk'      => 'KCMPKD',
            'nama_cpmk'      => 'CPMK Delete',
            'deskripsi'      => 'deskripsi cpmkDelete',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        // Pilih CPL dan attach pivot dengan bobot
        $cplId = $this->mataKuliah->cpls->first()->cpl_id;
        $cpmk->cpls()->attach($cplId, ['bobot' => 25.00]);

        // 2. Query tabel pivot untuk mendapatkan ID record yang baru
        $pivotId = MataKuliahCpmkPivot::where('cpmk_id', $cpmk->cpmk_id)
            ->where('cpl_id', $cplId)
            ->value('cpmk_mata_kuliah_id');

        // 3. Panggil endpoint delete
        $payload = [
            'action'               => 'delete',
            'cpmk_mata_kuliah_id'  => $pivotId,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pemetaan-cpmk', $payload);

        // 4. Asersi response dan database
        $response->assertStatus(200)
            ->assertJson(['message' => 'Pemetaan CPMK berhasil dihapus.']);

        $this->assertDatabaseMissing('cpmk_mata_kuliah', [
            'cpmk_mata_kuliah_id' => $pivotId,
        ]);
    }
}
