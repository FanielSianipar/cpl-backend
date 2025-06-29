<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataKelasTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminProdiRole;
    protected $permission;
    protected $prodi;
    protected $mataKuliah;
    protected $dosen1;
    protected $dosen2;
    protected $dosen3;
    protected $dosenRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Siapkan Fakultas → Prodi → MataKuliah
        $fakultas = Fakultas::factory()->create();
        $this->prodi = Prodi::factory()->create(['fakultas_id' => $fakultas->fakultas_id,]);
        $this->mataKuliah = MataKuliah::factory()->create(['prodi_id' => $this->prodi->prodi_id,]);

        // Roles & Permissions
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission      = Permission::firstOrCreate(['name' => 'Mengelola data kelas', 'guard_name' => 'web']);
        $this->adminProdiRole->givePermissionTo($this->permission);

        // Buat Admin Prodi user
        $this->user = User::factory()->create([
            'prodi_id' => $this->prodi->prodi_id,
        ]);
        $this->user->assignRole($this->adminProdiRole);

        // Buat role Dosen & 3 dosen sample
        $this->dosenRole = Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);
        $this->dosen1 = User::factory()->create();
        $this->dosen2 = User::factory()->create();
        $this->dosen3 = User::factory()->create();
        foreach ([$this->dosen1, $this->dosen2, $this->dosen3] as $dosen) {
            $dosen->assignRole($this->dosenRole);
        }
    }

    /** Test mengambil seluruh data kelas. */
    public function test_view_all_data_kelas(): void
    {
        // Buat 2 kelas + attach dosen
        $kelas1 = Kelas::factory()->create([
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
        $kelas1->dosens()->attach([
            $this->dosen1->id => ['jabatan' => 'Dosen Utama']
        ]);

        $kelas2 = Kelas::factory()->create([
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);
        $kelas2->dosens()->attach([
            $this->dosen2->id => ['jabatan' => 'Pendamping Dosen 1']
        ]);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Semua data kelas berhasil diambil.'])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'kelas_id',
                        'kode_kelas',
                        'nama_kelas',
                        'semester',
                        'tahun_ajaran',
                        'mata_kuliah_id',
                        'mata_kuliah' => ['mata_kuliah_id', 'kode_mata_kuliah', 'nama_mata_kuliah'],
                        'dosens'      => [
                            '*' => ['id', 'name', 'pivot' => ['jabatan']]
                        ]
                    ]
                ]
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    /** Test mengambil detail satu kelas berdasarkan ID. */
    public function test_view_detail_data_kelas_berdasarkan_id(): void
    {
        $kelas = Kelas::factory()->create(['mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,]);
        $kelas->dosens()->attach([
            $this->dosen3->id => ['jabatan' => 'Pendamping Dosen 2']
        ]);

        $payload = [
            'action'   => 'view',
            'kelas_id' => $kelas->kelas_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data kelas berhasil diambil.',
                'data' => ['kelas_id' => $kelas->kelas_id]
            ])
            ->assertJsonStructure([
                'data' => [
                    'dosens' => [
                        '*' => ['id', 'name', 'pivot' => ['jabatan']]
                    ]
                ]
            ]);

        $detail = $response->json('data');
        $this->assertEquals($kelas->kelas_id, $detail['kelas_id']);
        $this->assertCount(1, $detail['dosens']);
    }

    /** Test pembuatan data kelas beserta dosen pengampu berhasil. */
    public function test_store_data_kelas_berhasil(): void
    {
        $payload = [
            'action'         => 'store',
            'kode_kelas'     => 'K999',
            'nama_kelas'     => 'Store Kelas',
            'semester'       => 1,
            'tahun_ajaran'   => '2025/2026',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'dosens' => [
                ['dosen_id' => $this->dosen1->id, 'jabatan' => 'Dosen Utama'],
                ['dosen_id' => $this->dosen2->id, 'jabatan' => 'Pendamping Dosen 1'],
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Data kelas berhasil dibuat.'])
            ->assertJsonStructure([
                'data' => [
                    'kelas_id',
                    'dosens' => [['id', 'name', 'pivot' => ['jabatan']]]
                ]
            ]);

        $this->assertDatabaseHas('kelas', [
            'kode_kelas'     => 'K999',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
        ]);

        // Pivot kelas_dosen harus tercipta
        $this->assertDatabaseHas('kelas_dosen', [
            'kelas_id' => $response->json('data.kelas_id'),
            'dosen_id' => $this->dosen1->id,
            'jabatan'  => 'Dosen Utama',
        ]);
        $this->assertDatabaseHas('kelas_dosen', [
            'kelas_id' => $response->json('data.kelas_id'),
            'dosen_id' => $this->dosen2->id,
            'jabatan'  => 'Pendamping Dosen 1',
        ]);
    }

    /** Test validasi gagal saat pembuatan data kelas. */
    public function test_store_data_kelas_validasi_gagal(): void
    {
        $payload = [
            'action'         => 'store',
            'kode_kelas'     => '',
            'nama_kelas'     => '',
            'semester'       => '',
            'tahun_ajaran'   => '',
            'mata_kuliah_id' => '',
            'dosens'         => [['dosen_id' => '', 'jabatan' => '']]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'kode_kelas',
                'nama_kelas',
                'semester',
                'tahun_ajaran',
                'mata_kuliah_id',
                'dosens.0.dosen_id',
                'dosens.0.jabatan'
            ]);
    }

    /** Test update data kelas dan dosen pengampu berhasil. */
    public function test_update_data_kelas_berhasil(): void
    {
        $kelas = Kelas::factory()->create(['mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,]);

        $payload = [
            'action'         => 'update',
            'kelas_id'       => $kelas->kelas_id,
            'kode_kelas'     => 'K888',
            'nama_kelas'     => 'Updated Kelas',
            'semester'       => 2,
            'tahun_ajaran'   => '2026/2027',
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'dosens' => [
                ['dosen_id' => $this->dosen2->id, 'jabatan' => 'Pendamping Dosen 1'],
                ['dosen_id' => $this->dosen3->id, 'jabatan' => 'Pendamping Dosen 2'],
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Data kelas berhasil diperbarui.'])
            ->assertJsonStructure([
                'data' => ['dosens' => [['id', 'name', 'pivot' => ['jabatan']]]]
            ]);

        // Cek update kelas
        $this->assertDatabaseHas('kelas', [
            'kelas_id'       => $kelas->kelas_id,
            'kode_kelas'     => 'K888',
            'nama_kelas'     => 'Updated Kelas',
            'semester'       => 2,
            'tahun_ajaran'   => '2026/2027',
        ]);

        // Cek pivot sync: dosen1 harus hilang, dosen2 & 3 ada
        $this->assertDatabaseMissing('kelas_dosen', [
            'kelas_id' => $kelas->kelas_id,
            'dosen_id' => $this->dosen1->id
        ]);
        $this->assertDatabaseHas('kelas_dosen', [
            'kelas_id' => $kelas->kelas_id,
            'dosen_id' => $this->dosen2->id,
            'jabatan'  => 'Pendamping Dosen 1'
        ]);
        $this->assertDatabaseHas('kelas_dosen', [
            'kelas_id' => $kelas->kelas_id,
            'dosen_id' => $this->dosen3->id,
            'jabatan'  => 'Pendamping Dosen 2'
        ]);
    }

    /** Test validasi gagal saat update data kelas. */
    public function test_update_data_kelas_validasi_gagal(): void
    {
        $kelas = Kelas::factory()->create(['mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,]);

        $payload = [
            'action'         => 'update',
            'kelas_id'       => $kelas->kelas_id,
            'kode_kelas'     => '',
            'nama_kelas'     => '',
            'semester'       => '',
            'tahun_ajaran'   => '',
            'mata_kuliah_id' => 9999,
            'dosens'         => [['dosen_id' => '', 'jabatan' => '']]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'kode_kelas',
                'nama_kelas',
                'semester',
                'tahun_ajaran',
                'mata_kuliah_id',
                'dosens.0.dosen_id',
                'dosens.0.jabatan'
            ]);
    }

    /** Test penghapusan data kelas. */
    public function test_delete_data_kelas_berhasil(): void
    {
        $kelas = Kelas::factory()->create(['mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,]);

        $payload = [
            'action'   => 'delete',
            'kelas_id' => $kelas->kelas_id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-data-kelas', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Data kelas berhasil dihapus.']);

        $this->assertDatabaseMissing('kelas', [
            'kelas_id' => $kelas->kelas_id,
        ]);
    }
}
