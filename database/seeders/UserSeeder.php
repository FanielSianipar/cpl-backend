<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\User;
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
        $adminUniversitasRole = Role::firstOrCreate([
            'name' => 'Admin Universitas',
            'guard_name' => 'web'
        ]);

        // Role untuk user non-admin: Admin Prodi, Kaprodi, dan Dosen.
        $adminProdiRole = Role::firstOrCreate([
            'name' => 'Admin Prodi',
            'guard_name' => 'web'
        ]);
        $kaprodiRole = Role::firstOrCreate([
            'name' => 'Kaprodi',
            'guard_name' => 'web'
        ]);
        $dosenRole = Role::firstOrCreate([
            'name' => 'Dosen',
            'guard_name' => 'web'
        ]);

        $nonAdminRoles = [$adminProdiRole, $kaprodiRole, $dosenRole];

        // Buat 3 user dengan role Admin Universitas (prodi_id = null)
        $adminUsers = User::factory()->count(3)->create([
            'prodi_id' => null, // User Admin Universitas tidak terikat ke prodi
        ]);

        foreach ($adminUsers as $user) {
            $user->assignRole($adminUniversitasRole);
        }

        $prodiIds = Prodi::pluck('prodi_id')->toArray();

        // Buat 17 user dan tetapkan prodi_id secara round-robin dari array $prodiIds
        User::factory()->count(17)->make()->each(function ($user, $index) use ($prodiIds, $nonAdminRoles) {
            $user->prodi_id = $prodiIds[$index % count($prodiIds)];
            $user->save();
            // Memberikan salah satu role non-admin secara acak
            $user->assignRole(Arr::random($nonAdminRoles));
        });
    }
}
