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
        Permission::create(['name' => 'Mengelola akun admin universitas']);
        Permission::create(['name' => 'Mengelola akun admin prodi']);
        Permission::create(['name' => 'Mengelola akun kaprodi']);
        Permission::create(['name' => 'Mengelola akun dosen']);
        Permission::create(['name' => 'Mengelola data mahasiswa']);
        Permission::create(['name' => 'Mengelola data mata kuliah']);
        Permission::create(['name' => 'Mengelola data CPL']);
        Permission::create(['name' => 'Mengelola data CPMK']);
        Permission::create(['name' => 'Melakukan pemetaan CPL']);
        Permission::create(['name' => 'Melakukan pemetaan CPMK']);
        Permission::create(['name' => 'Memasukkan bobot CPL']);
        Permission::create(['name' => 'Memasukkan bobot CPMK']);
        Permission::create(['name' => 'Mengelola data nilai mahasiswa']);
        Permission::create(['name' => 'Melihat hasil perhitungan']);

        // Roles
        $adminUniversitas = Role::create(['name' => 'admin universitas']);
        $adminProdi = Role::create(['name' => 'admin prodi']);
        $kaprodi = Role::create(['name' => 'kaprodi']);
        $dosen = Role::create(['name' => 'dosen']);

        // Assign permissions to roles
        $adminUniversitas->givePermissionTo([
            'Mengelola akun admin universitas',
            'Mengelola akun admin prodi',
            'Melihat hasil perhitungan',
        ]);

        $adminProdi->givePermissionTo([
            'Mengelola akun kaprodi',
            'Mengelola akun dosen',
            'Mengelola data mahasiswa',
            'Mengelola data mata kuliah',
            'Mengelola data CPL',
            'Mengelola data CPMK',
            'Melihat hasil perhitungan',
        ]);

        $kaprodi->givePermissionTo([
            'Melakukan pemetaan CPL',
            'Melakukan pemetaan CPMK',
        ]);

        $dosen->givePermissionTo([
            'Melakukan pemetaan CPL',
            'Melakukan pemetaan CPMK',
            'Memasukkan bobot CPL',
            'Memasukkan bobot CPMK',
            'Mengelola data nilai mahasiswa',
        ]);
    }
}
