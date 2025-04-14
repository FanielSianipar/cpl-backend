<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat data user dummy
        $users = User::factory()->count(10)->create();

        // Mengambil semua nama role yang telah didaftarkan via RoleSeeder
        $roleNames = Role::pluck('name')->toArray();

        // Assign role secara acak ke masing-masing user
        foreach ($users as $user) {
            if (!empty($roleNames)) {
                // Menggunakan helper Arr untuk memilih role secara acak
                $user->assignRole(Arr::random($roleNames));
            }
        }
    }
}
