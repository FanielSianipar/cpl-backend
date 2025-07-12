<?php

namespace Tests\Feature;

use App\Models\CPMK;
use App\Models\CPL;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Penilaian;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\SubPenilaian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SubPenilaianTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminProdiRole;
    protected $permission;
    protected $prodi;
    protected $mataKuliah;
    protected $kelas;
    protected $penilaian;
    protected $cpl;
    protected $cpmk;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Siapkan Fakultas → Prodi → MataKuliah
        $fakultas = Fakultas::factory()->create();
        $this->prodi = Prodi::factory()->create(['fakultas_id' => $fakultas->fakultas_id]);
        $this->mataKuliah = MataKuliah::factory()->create(['prodi_id' => $this->prodi->prodi_id]);

        // Roles & Permissions
        $this->adminProdiRole = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola sub penilaian', 'guard_name' => 'web']);
        $this->adminProdiRole->givePermissionTo($this->permission);

        // Buat Admin Prodi user
        $this->user = User::factory()->create([
            'prodi_id' => $this->prodi->prodi_id
        ]);
        $this->user->assignRole($this->adminProdiRole);

        // Buat Kelas
        $this->kelas = Kelas::factory()->create([
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id
        ]);

        // Buat Penilaian
        $this->penilaian = Penilaian::factory()->create();

        // Buat CPL dan CPMK
        $this->cpl = CPL::factory()->create(['prodi_id' => $this->prodi->prodi_id]);
        $this->cpmk = CPMK::factory()->create(['prodi_id' => $this->prodi->prodi_id]);

        // Setup relasi CPMK-MataKuliah dengan bobot
        $this->mataKuliah->cpmks()->attach($this->cpmk->cpmk_id, [
            'cpl_id' => $this->cpl->cpl_id,
            'bobot' => 100.00
        ]);
    }

    /** Test mengambil seluruh data sub penilaian. */
    public function test_view_all_sub_penilaian(): void
    {
        // Buat 2 sub penilaian + attach CPMK
        $subPenilaian1 = SubPenilaian::factory()->create([
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id
        ]);
        $subPenilaian1->cpmks()->attach($this->cpmk->cpmk_id, [
            'cpl_id' => $this->cpl->cpl_id,
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'bobot' => 100.00
        ]);

        $subPenilaian2 = SubPenilaian::factory()->create([
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id
        ]);
        $subPenilaian2->cpmks()->attach($this->cpmk->cpmk_id, [
            'cpl_id' => $this->cpl->cpl_id,
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'bobot' => 100.00
        ]);

        $payload = ['action' => 'view'];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-sub-penilaian', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Semua data sub-penilaian berhasil diambil.'])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['sub_penilaian_id', 'nama_sub_penilaian', 'penilaian_id', 'kelas_id']
                ]
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    /** Test mengambil detail satu sub penilaian berdasarkan ID. */
    public function test_view_detail_sub_penilaian_berdasarkan_id(): void
    {
        $subPenilaian = SubPenilaian::factory()->create([
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id
        ]);
        $subPenilaian->cpmks()->attach($this->cpmk->cpmk_id, [
            'cpl_id' => $this->cpl->cpl_id,
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'bobot' => 100.00
        ]);

        $payload = [
            'action' => 'view',
            'sub_penilaian_id' => $subPenilaian->sub_penilaian_id
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-sub-penilaian', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data sub-penilaian berhasil diambil.'
            ]);

        $data = $response->json('data.0'); // Ambil data pertama dari array
        $this->assertEquals($subPenilaian->sub_penilaian_id, $data['sub_penilaian_id']);
        $this->assertEquals($subPenilaian->nama_sub_penilaian, $data['nama_sub_penilaian']);
        $this->assertCount(1, $data['cpmks']);

        // Verifikasi data CPMK
        $cpmk = $data['cpmks'][0];
        $this->assertEquals($this->cpmk->cpmk_id, $cpmk['cpmk_id']);
        $this->assertEquals(100.00, $cpmk['pivot']['bobot']);
    }

    /** Test pembuatan data sub penilaian berhasil. */
    public function test_store_sub_penilaian_berhasil(): void
    {
        $payload = [
            'action' => 'store',
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id,
            'nama_sub_penilaian' => 'Sub Penilaian Test',
            'cpmks' => [
                [
                    'cpmk_id' => $this->cpmk->cpmk_id,
                    'cpl_id' => $this->cpl->cpl_id,
                    'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
                    'bobot' => 100.00
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-sub-penilaian', $payload);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Sub-penilaian berhasil ditambahkan.'])
            ->assertJsonStructure([
                'data' => [
                    'sub_penilaian_id',
                    'cpmks'
                ]
            ]);

        $this->assertDatabaseHas('sub_penilaian', [
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id,
            'nama_sub_penilaian' => 'Sub Penilaian Test'
        ]);

        $this->assertDatabaseHas('sub_penilaian_cpmk_mata_kuliah', [
            'sub_penilaian_id' => $response->json('data.sub_penilaian_id'),
            'cpmk_id' => $this->cpmk->cpmk_id,
            'cpl_id' => $this->cpl->cpl_id,
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'bobot' => 100.00
        ]);
    }

    /** Test validasi gagal saat pembuatan data sub penilaian. */
    public function test_store_sub_penilaian_validasi_gagal(): void
    {
        $payload = [
            'action' => 'store',
            'penilaian_id' => '',
            'kelas_id' => '',
            'nama_sub_penilaian' => '',
            'cpmks' => [
                ['cpmk_id' => '', 'cpl_id' => '', 'mata_kuliah_id' => '', 'bobot' => '']
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-sub-penilaian', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'penilaian_id',
                'kelas_id',
                'nama_sub_penilaian',
                'cpmks.0.cpmk_id',
                'cpmks.0.cpl_id',
                'cpmks.0.mata_kuliah_id',
                'cpmks.0.bobot'
            ]);
    }

    /** Test update data sub penilaian berhasil. */
    public function test_update_sub_penilaian_berhasil(): void
    {
        // Buat sub penilaian awal
        $subPenilaian = SubPenilaian::factory()->create([
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id,
            'nama_sub_penilaian' => 'Sub Penilaian Test'
        ]);

        // Tambahkan mapping CPMK awal
        $subPenilaian->cpmks()->attach($this->cpmk->cpmk_id, [
            'cpl_id' => $this->cpl->cpl_id,
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'bobot' => 50.00
        ]);

        // Refresh model untuk memastikan relasi ter-load
        $subPenilaian = $subPenilaian->fresh(['cpmks']);

        $payload = [
            'action' => 'update',
            'sub_penilaian_id' => $subPenilaian->sub_penilaian_id,
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id,
            'nama_sub_penilaian' => 'Updated Sub Penilaian',
            'cpmks' => [
                [
                    'cpmk_id' => $this->cpmk->cpmk_id,
                    'cpl_id' => $this->cpl->cpl_id,
                    'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
                    'bobot' => 100.00
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-sub-penilaian', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Sub-penilaian berhasil diperbarui.'
            ]);

        // Verifikasi perubahan data utama
        $this->assertDatabaseHas('sub_penilaian', [
            'sub_penilaian_id' => $subPenilaian->sub_penilaian_id,
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id,
            'nama_sub_penilaian' => 'Updated Sub Penilaian'
        ]);

        // Verifikasi perubahan relasi CPMK
        $this->assertDatabaseHas('sub_penilaian_cpmk_mata_kuliah', [
            'sub_penilaian_id' => $subPenilaian->sub_penilaian_id,
            'cpmk_id' => $this->cpmk->cpmk_id,
            'cpl_id' => $this->cpl->cpl_id,
            'mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id,
            'bobot' => 100.00
        ]);

        // Verifikasi data yang diupdate
        $updatedSubPenilaian = SubPenilaian::with('cpmks')->find($subPenilaian->sub_penilaian_id);
        $this->assertEquals('Updated Sub Penilaian', $updatedSubPenilaian->nama_sub_penilaian);
        $this->assertEquals(100.00, $updatedSubPenilaian->cpmks[0]->pivot->bobot);
    }

    /** Test validasi gagal saat update data sub penilaian. */
    public function test_update_sub_penilaian_validasi_gagal(): void
    {
        $subPenilaian = SubPenilaian::factory()->create([
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id
        ]);

        $payload = [
            'action' => 'update',
            'sub_penilaian_id' => $subPenilaian->sub_penilaian_id,
            'penilaian_id' => '',
            'kelas_id' => '',
            'nama_sub_penilaian' => '',
            'cpmks' => [
                ['cpmk_id' => '', 'cpl_id' => '', 'mata_kuliah_id' => '', 'bobot' => '']
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-sub-penilaian', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'penilaian_id',
                'kelas_id',
                'nama_sub_penilaian',
                'cpmks.0.cpmk_id',
                'cpmks.0.cpl_id',
                'cpmks.0.mata_kuliah_id',
                'cpmks.0.bobot'
            ]);
    }

    /** Test penghapusan data sub penilaian. */
    public function test_delete_sub_penilaian_berhasil(): void
    {
        $subPenilaian = SubPenilaian::factory()->create([
            'penilaian_id' => $this->penilaian->penilaian_id,
            'kelas_id' => $this->kelas->kelas_id
        ]);

        $payload = [
            'action' => 'delete',
            'sub_penilaian_id' => $subPenilaian->sub_penilaian_id
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/kelola-sub-penilaian', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Sub-penilaian berhasil dihapus.']);

        $this->assertDatabaseMissing('sub_penilaian', [
            'sub_penilaian_id' => $subPenilaian->sub_penilaian_id
        ]);
    }
}
