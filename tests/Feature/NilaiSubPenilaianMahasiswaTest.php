<?php

namespace Tests\Feature;

use App\Models\CPMK;
use App\Models\CPL;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\NilaiSubPenilaianMahasiswa;
use App\Models\Penilaian;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\SubPenilaian;
use App\Models\SubPenilaianCpmkMataKuliah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NilaiSubPenilaianMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    protected $dosen;
    protected $roleDosen;
    protected $permission;
    protected $kelas;
    protected $fakultas;
    protected $prodi;
    protected $mataKuliah;
    protected $penilaian;
    protected $cpl;
    protected $cpmk;
    protected $subPenilaian;
    protected $pivot;
    protected $mahasiswa1;
    protected $mahasiswa2;

    protected function setUp(): void
    {
        parent::setUp();

        // buat hierarki fakultas → prodi → mata kuliah → kelas
        $this->fakultas   = Fakultas::factory()->create();
        $this->prodi      = Prodi::factory()->create(['fakultas_id' => $this->fakultas->fakultas_id]);
        $this->mataKuliah = MataKuliah::factory()->create(['prodi_id' => $this->prodi->prodi_id]);
        $this->kelas      = Kelas::factory()->create(['mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id]);

        // siapkan penilaian, CPL & CPMK
        $this->penilaian = Penilaian::factory()->create();
        $this->cpl       = CPL::factory()->create(['prodi_id' => $this->prodi->prodi_id]);
        $this->cpmk      = CPMK::factory()->create(['mata_kuliah_id' => $this->mataKuliah->mata_kuliah_id]);

        // buat sub-penilaian dan mapping pivot CPMK–CPL
        $this->subPenilaian = SubPenilaian::factory()->create([
            'penilaian_id'       => $this->penilaian->penilaian_id,
            'kelas_id'           => $this->kelas->kelas_id,
            'nama_sub_penilaian' => 'SubTest',
        ]);
        $this->subPenilaian->cpmks()->attach($this->cpmk->cpmk_id, [
            'cpl_id' => $this->cpl->cpl_id,
            'bobot'  => 10.00,
        ]);

        // ambil model pivot untuk sub-penilaian–CPMK–CPL
        $this->pivot = SubPenilaianCpmkMataKuliah::first();

        // siapkan dua mahasiswa dan enroll ke kelas
        $this->mahasiswa1 = Mahasiswa::factory()->create();
        $this->mahasiswa2 = Mahasiswa::factory()->create();
        DB::table('kelas_mahasiswa')->insert([
            ['kelas_id' => $this->kelas->kelas_id, 'mahasiswa_id' => $this->mahasiswa1->mahasiswa_id],
            ['kelas_id' => $this->kelas->kelas_id, 'mahasiswa_id' => $this->mahasiswa2->mahasiswa_id],
        ]);

        // siapkan user Dosen dengan role & pivot kelas_dosen
        $this->roleDosen = Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data nilai mahasiswa', 'guard_name' => 'web']);
        $this->roleDosen->givePermissionTo($this->permission);

        $this->dosen = User::factory()->create();
        $this->dosen->assignRole($this->roleDosen);

        DB::table('kelas_dosen')->insert([
            'kelas_id'   => $this->kelas->kelas_id,
            'dosen_id'   => $this->dosen->id,
            'jabatan'    => 'Dosen Utama',
        ]);
    }

    /** Test view semua nilai sub-penilaian mahasiswa di kelas & pivot tertentu. */
    public function test_view_all_nilai_di_kelas(): void
    {
        // siapkan 2 record nilai
        NilaiSubPenilaianMahasiswa::create([
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                      => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'                      => 80.00,
            'nilai_terbobot'                    => 8.00,
        ]);
        NilaiSubPenilaianMahasiswa::create([
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                      => $this->mahasiswa2->mahasiswa_id,
            'nilai_mentah'                      => 90.00,
            'nilai_terbobot'                    => 9.00,
        ]);

        $payload = [
            'action'                             => 'view',
            'kelas_id'                           => $this->kelas->kelas_id,
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
        ];

        $response = $this->actingAs($this->dosen)
            ->postJson('/api/kelola-nilai-mahasiswa', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Nilai berhasil diambil.'])
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'nilai_sub_penilaian_mahasiswa_id',
                        'sub_penilaian_cpmk_mata_kuliah_id',
                        'mahasiswa_id',
                        'nilai_mentah',
                        'nilai_terbobot',
                        'mahasiswa' => [
                            'mahasiswa_id',
                        ]
                    ]
                ]
            ]);
    }

    /** Test view detail nilai untuk satu mahasiswa. */
    public function test_view_detail_nilai_mahasiswa_berdasarkan_mahasiswa_id(): void
    {
        $record = NilaiSubPenilaianMahasiswa::create([
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                      => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'                      => 75.00,
            'nilai_terbobot'                    => 7.50,
        ]);

        $payload = [
            'action'                             => 'view',
            'kelas_id'                           => $this->kelas->kelas_id,
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                       => $this->mahasiswa1->mahasiswa_id,
        ];

        $response = $this->actingAs($this->dosen)
            ->postJson('/api/kelola-nilai-mahasiswa', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Nilai berhasil diambil.'])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'mahasiswa_id' => $this->mahasiswa1->mahasiswa_id,
                'nilai_mentah' => 75.00,
                'nilai_terbobot' => 7.50,
            ]);
    }

    /** Test store nilai sub-penilaian mahasiswa berhasil. */
    public function test_store_nilai_berhasil(): void
    {
        $payload = [
            'action'                             => 'store',
            'kelas_id'                           => $this->kelas->kelas_id,
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                       => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'                       => 85.00,
        ];

        $response = $this->actingAs($this->dosen)
            ->postJson('/api/kelola-nilai-mahasiswa', $payload);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Nilai berhasil ditambahkan.'])
            ->assertJsonStructure(['data' => ['nilai_sub_penilaian_mahasiswa_id', 'nilai_mentah', 'nilai_terbobot']]);

        $this->assertDatabaseHas('nilai_sub_penilaian_mahasiswa', [
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                      => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'                      => 85.00,
            'nilai_terbobot'                    => 8.50,
        ]);
    }

    /** Test validasi gagal saat store nilai. */
    public function test_store_nilai_validasi_gagal(): void
    {
        $payload = [
            'action'                             => 'store',
            'kelas_id'                           => '',
            'sub_penilaian_cpmk_mata_kuliah_id' => '',
            'mahasiswa_id'                       => '',
            'nilai_mentah'                       => '',
        ];

        $response = $this->actingAs($this->dosen)
            ->postJson('/api/kelola-nilai-mahasiswa', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'kelas_id',
                'sub_penilaian_cpmk_mata_kuliah_id',
                'mahasiswa_id',
                'nilai_mentah'
            ]);
    }

    /** Test update nilai sub-penilaian mahasiswa berhasil. */
    public function test_update_nilai_berhasil(): void
    {
        $record = NilaiSubPenilaianMahasiswa::create([
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                      => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'                      => 60.00,
            'nilai_terbobot'                    => 6.00,
        ]);

        $payload = [
            'action'                             => 'update',
            'kelas_id'                           => $this->kelas->kelas_id,
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                       => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'                       => 70.00,
        ];

        $response = $this->actingAs($this->dosen)
            ->postJson('/api/kelola-nilai-mahasiswa', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Nilai berhasil diperbarui.']);

        $this->assertDatabaseHas('nilai_sub_penilaian_mahasiswa', [
            'mahasiswa_id'    => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'    => 70.00,
            'nilai_terbobot'  => 7.00,
        ]);
    }

    /** Test validasi gagal saat update nilai. */
    public function test_update_nilai_validasi_gagal(): void
    {
        $payload = [
            'action'                             => 'update',
            'kelas_id'                           => '',
            'sub_penilaian_cpmk_mata_kuliah_id' => '',
            'mahasiswa_id'                       => '',
            'nilai_mentah'                       => '',
        ];

        $response = $this->actingAs($this->dosen)
            ->postJson('/api/kelola-nilai-mahasiswa', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'kelas_id',
                'sub_penilaian_cpmk_mata_kuliah_id',
                'mahasiswa_id',
                'nilai_mentah'
            ]);
    }

    /** Test delete nilai sub-penilaian mahasiswa. */
    public function test_delete_nilai(): void
    {
        // Siapkan record nilai
        $record = NilaiSubPenilaianMahasiswa::create([
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                      => $this->mahasiswa1->mahasiswa_id,
            'nilai_mentah'                      => 50.00,
            'nilai_terbobot'                    => 5.00,
        ]);

        // Payload untuk delete
        $payload = [
            'action'                             => 'delete',
            'kelas_id'                           => $this->kelas->kelas_id,
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                       => $this->mahasiswa1->mahasiswa_id,
        ];

        // Kirim permintaan delete
        $response = $this->actingAs($this->dosen)
            ->postJson('/api/kelola-nilai-mahasiswa', $payload);

        // Verifikasi respons dan database
        $response->assertStatus(200)
            ->assertJson(['message' => 'Nilai berhasil dihapus.']);

        $this->assertDatabaseMissing('nilai_sub_penilaian_mahasiswa', [
            'sub_penilaian_cpmk_mata_kuliah_id' => $this->pivot->sub_penilaian_cpmk_mata_kuliah_id,
            'mahasiswa_id'                       => $this->mahasiswa1->mahasiswa_id,
        ]);
    }
}
