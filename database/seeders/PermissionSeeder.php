<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Permissions
        Permission::create(['name' => 'Mengelola data prodi']);
        Permission::create(['name' => 'Mengelola akun admin universitas']);
        Permission::create(['name' => 'Mengelola akun admin prodi']);
        Permission::create(['name' => 'Mengelola akun kaprodi']);
        Permission::create(['name' => 'Mengelola akun dosen']);
        Permission::create(['name' => 'Mengelola data mahasiswa']);
        Permission::create(['name' => 'Mengelola data mata kuliah']);
        Permission::create(['name' => 'Mengelola data kelas']);
        Permission::create(['name' => 'Mengelola data CPL']);
        Permission::create(['name' => 'Mengelola data CPMK']);
        Permission::create(['name' => 'Melakukan pemetaan CPL']);
        Permission::create(['name' => 'Melakukan pemetaan CPMK']);
        Permission::create(['name' => 'Mengelola sub penilaian']);
        Permission::create(['name' => 'Mengelola data nilai mahasiswa']);
        Permission::create(['name' => 'Melihat hasil perhitungan']);

        // Assign permissions to existing roles
        $adminUniversitas = Role::where('name', 'Admin Universitas')->first();
        $adminProdi = Role::where('name', 'Admin Prodi')->first();
        $kaprodi = Role::where('name', 'Kaprodi')->first();
        $dosen = Role::where('name', 'Dosen')->first();

        if ($adminUniversitas) {
            $adminUniversitas->givePermissionTo([
                'Mengelola data prodi',
                'Mengelola akun admin universitas',
                'Mengelola akun admin prodi',
                'Melihat hasil perhitungan',
            ]);
        }

        if ($adminProdi) {
            $adminProdi->givePermissionTo([
                'Mengelola akun kaprodi',
                'Mengelola akun dosen',
                'Mengelola data mahasiswa',
                'Mengelola data mata kuliah',
                'Mengelola data kelas',
                'Mengelola data CPL',
                'Mengelola data CPMK',
                'Melakukan pemetaan CPL',
                'Melakukan pemetaan CPMK',
                'Mengelola sub penilaian',
                'Melihat hasil perhitungan',
            ]);
        }

        if ($kaprodi) {
            $kaprodi->givePermissionTo([
                'Melihat hasil perhitungan',
            ]);
        }

        if ($dosen) {
            $dosen->givePermissionTo([
                'Mengelola sub penilaian',
                'Mengelola data nilai mahasiswa',
            ]);
        }
    }
}
