<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role sudah ada. Jika belum, buat terlebih dahulu.
        Role::firstOrCreate(['name' => 'Admin Universitas']);
        Role::firstOrCreate(['name' => 'Admin Prodi']);
        Role::firstOrCreate(['name' => 'Kaprodi']);
        Role::firstOrCreate(['name' => 'Dosen']);
    }
}
