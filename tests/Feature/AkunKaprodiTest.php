<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AkunKaprodiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cache permission untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Test mengambil seluruh akun Admin Prodi.
     */
    public function test_view_all_akun_kaprodi(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun kaprodi');

        $payload = [
            'action' => 'view'
        ];
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-kaprodi', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Semua data akun Kaprodi berhasil diambil.'
            ]);
    }

    /**
     * Test mengambil detail satu akun Admin Prodi berdasarkan id.
     */
    public function test_view_detail_akun_kaprodi(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun kaprodi');

        // Buat akun Kaprodi yang akan diambil datanya.
        $kaprodi = User::factory()->create([
            'name' => 'Kaprodi Test',
            'email' => 'kaprodi@example.com'
        ]);
        $kaprodi->assignRole('Kaprodi');

        $payload = [
            'action' => 'view',
            'id'     => $kaprodi->id
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data akun Kaprodi berhasil diambil.'
            ]);

        // Pastikan data yang dikembalikan benar (cukup cek email dan id saja).
        $data = $response->json('data');
        $this->assertEquals($kaprodi->id, $data['id']);
        $this->assertEquals($kaprodi->email, $data['email']);
    }

    public function test_store_akun_kaprodi(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun kaprodi');

        $payload = [
            'action'   => 'store',
            'name'     => 'Kaprodi Baru',
            'email'    => 'kaprodi@example.com',
            'password' => 'password123'
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Akun Kaprodi berhasil dibuat.'
            ]);

        // Pastikan data benar-benar masuk ke database
        $this->assertDatabaseHas('users', [
            'email' => 'kaprodi@example.com'
        ]);
    }

    public function test_store_akun_kaprodi_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun kaprodi');

        $payload = [
            'action'   => 'store',
            'name' => '', // Kosong, harusnya invalid
            'email' => 'invalid-email', // Format salah
            'password' => 'short' // Password kurang dari 8 karakter
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_update_akun_kaprodi_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun kaprodi');

        // Buat akun kaprodi yang akan diupdate
        $roleKaprodi = Role::firstOrCreate(['name' => 'Kaprodi', 'guard_name' => 'web']);
        $kaprodi = User::factory()->create([
            'name'  => 'Kaprodi Old Name',
            'email' => 'kaprodiold@example.com',
        ]);
        $kaprodi->assignRole($roleKaprodi);

        $payload = [
            'action'                => 'update',
            'id'                    => $kaprodi->id,
            'name'                  => 'Kaprodi New Name',
            'email'                 => 'kaprodinew@example.com',
            'password'              => 'newpassword123'
        ];

        // Lakukan request update dengan method POST sesuai dengan route Anda
        $response = $this->actingAs($user)->postJson('/api/kelola-akun-kaprodi', $payload);

        // Pastikan response berhasil dengan status 200 dan pesan yang sesuai
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Kaprodi berhasil diperbarui.'
            ]);

        // Pastikan data pada database sudah terupdate
        $this->assertDatabaseHas('users', [
            'id'    => $kaprodi->id,
            'name'  => 'Kaprodi New Name',
            'email' => 'kaprodinew@example.com',
        ]);
    }

    public function test_update_akun_kaprodi_validasi_gagal(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun kaprodi');

        // Buat akun Kaprodi yang akan diupdate
        $roleKaprodi = Role::firstOrCreate(['name' => 'Kaprodi', 'guard_name' => 'web']);
        $kaprodi = User::factory()->create([
            'name'  => 'Kaprodi To Update',
            'email' => 'kaprodi@example.com',
        ]);
        $kaprodi->assignRole($roleKaprodi);

        $payload = [
            'action'                => 'update',
            'id'                    => $kaprodi->id,
            'name'                  => '', // Kosong, harusnya invalid
            'email'                 => 'invalid-email', // Format salah
            'password'              => 'short' // Password kurang dari 8 karakter
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_delete_akun_kaprodi_berhasil(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Mengelola akun kaprodi', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $role->givePermissionTo('Mengelola akun kaprodi');

        // Buat akun Kaprodi yang akan dihapus
        $roleKaprodi = Role::firstOrCreate(['name' => 'Kaprodi', 'guard_name' => 'web']);
        $kaprodi = User::factory()->create([
            'name'  => 'Kaprodi To Update',
            'email' => 'kaprodi@example.com',
        ]);
        $kaprodi->assignRole($roleKaprodi);

        $payload = [
            'action'                => 'delete',
            'id'                    => $kaprodi->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/kelola-akun-kaprodi', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun Kaprodi berhasil dihapus.'
            ]);

        // Pastikan data sudah terhapus dari database
        $this->assertDatabaseMissing('users', [
            'id'    => $kaprodi->id,
            'email' => $kaprodi->email
        ]);
    }
}
