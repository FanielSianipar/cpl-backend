<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $role;
    protected $permission;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset permission cache untuk menghindari error duplikat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat role dan permission secara global
        $this->role = Role::firstOrCreate(['name' => 'Admin Universitas', 'guard_name' => 'web']);
        $this->permission = Permission::firstOrCreate(['name' => 'Mengelola data prodi', 'guard_name' => 'web']);

        // Buat user dummy secara global dan assign role serta permission
        $this->user = User::factory()->create();
        $this->user->assignRole($this->role);
        $this->role->givePermissionTo($this->permission);
    }

    /**
     * Test untuk login sukses dengan kredensial yang valid.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        // Pastikan password yang dihasilkan factory adalah "password"
        $response = $this->postJson('/api/login', [
            'email'    => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    /**
     * Test untuk gagal login dengan kredensial yang tidak valid.
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'adminuniv@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized'
            ]);
    }

    /**
     * Test untuk logout: user yang telah autentikasi dapat logout dengan sukses.
     */
    public function test_authenticated_user_can_logout_successfully()
    {
        // Simulasikan user sudah login menggunakan Sanctum.
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out'
            ]);
    }
}
