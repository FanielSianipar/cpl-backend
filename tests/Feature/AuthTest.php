<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // Fungsi untuk set up lingkungan test
    protected function setUp(): void
    {
        parent::setUp();

        // Buat role untuk testing
        Role::create(['name' => 'Admin Universitas', 'Admin Prodi', 'Kaprodi', 'Dosen']);
    }

    // Contoh test untuk login sukses
    public function test_user_can_login_with_valid_credentials()
    {
        // Buat user dummy
        $user = User::factory()->create();
        $roleNames = Role::pluck('name')->toArray();
        $user->assignRole(Arr::random($roleNames));

        // Kirim request login
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Pastikan respons sukses
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    // Contoh test untuk gagal login
    public function test_user_cannot_login_with_invalid_credentials()
    {
        // Buat user dummy
        $user = User::factory()->create();
        $roleNames = Role::pluck('name')->toArray();
        $user->assignRole(Arr::random($roleNames));

        // Kirim request login dengan password salah
        $response = $this->postJson('/api/login', [
            'email' => 'adminuniv@example.com',
            'password' => 'wrongpassword',
        ]);

        // Pastikan respons gagal
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized'
            ]);
    }

    // Contoh test untuk logout
    public function test_authenticated_user_can_logout_successfully()
    {
        // Buat user dummy
        $user = User::factory()->create();

        // Mendaftarkan role secara acak
        $roleNames = Role::pluck('name')->toArray();
        $user->assignRole(Arr::random($roleNames));

        // Simulasikan login
        Sanctum::actingAs($user, ['*']);

        // Kirim request logout
        $response = $this->postJson('/api/logout');

        // Pastikan respons logout sukses
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out'
            ]);
    }
}
