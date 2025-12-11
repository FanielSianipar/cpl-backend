<?php

use App\Http\Controllers\AdminProdiController;
use App\Http\Controllers\AdminUniversitasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KaprodiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route login (tanpa middleware auth:sanctum)
Route::post('/login', [AuthController::class, 'login']);

// Endpoint lain yang dilindungi middleware auth:sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Admin Universitas
    // Kelola data prodi
    Route::middleware(['role:Admin Universitas', 'permission:Mengelola data prodi'])->group(function () {
        Route::get('/data-fakultas', [AdminUniversitasController::class, 'dataFakultas']);
        Route::post('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
        Route::get('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
        Route::put('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
        Route::delete('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
    });
    // Kelola akun admin universitas
    Route::middleware(['role:Admin Universitas', 'permission:Mengelola akun admin universitas'])->group(function () {
        Route::get('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'kelolaAkunAdminUniversitas']);
        Route::post('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'kelolaAkunAdminUniversitas']);
        Route::put('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'kelolaAkunAdminUniversitas']);
        Route::delete('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'kelolaAkunAdminUniversitas']);
    });
    // Kelola akun admin prodi
    Route::middleware(['role:Admin Universitas', 'permission:Mengelola akun admin prodi'])->group(function () {
        Route::get('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'kelolaAkunAdminProdi']);
        Route::post('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'kelolaAkunAdminProdi']);
        Route::put('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'kelolaAkunAdminProdi']);
        Route::delete('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'kelolaAkunAdminProdi']);
    });
    // Lihat hasil perhitungan
    Route::middleware(['permission:view calculation results'])->group(function () {
        Route::get('/lihat-hasil-perhitungan', [AdminUniversitasController::class, 'viewCalculationResults']);
    });

    // Admin Prodi
    // Kelola akun kaprodi
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola akun kaprodi'])->group(function () {
        Route::get('/kelola-akun-kaprodi', [AdminProdiController::class, 'kelolaAkunKaprodi']);
        Route::post('/kelola-akun-kaprodi', [AdminProdiController::class, 'kelolaAkunKaprodi']);
        Route::put('/kelola-akun-kaprodi', [AdminProdiController::class, 'kelolaAkunKaprodi']);
        Route::delete('/kelola-akun-kaprodi', [AdminProdiController::class, 'kelolaAkunKaprodi']);
    });
    // Kelola akun dosen
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola akun dosen'])->group(function () {
        Route::get('/kelola-akun-dosen', [AdminProdiController::class, 'kelolaAkunDosen']);
        Route::post('/kelola-akun-dosen', [AdminProdiController::class, 'kelolaAkunDosen']);
        Route::put('/kelola-akun-dosen', [AdminProdiController::class, 'kelolaAkunDosen']);
        Route::delete('/kelola-akun-dosen', [AdminProdiController::class, 'kelolaAkunDosen']);
    });
    // Kelola data mahasiswa
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola data mahasiswa'])->group(function () {
        Route::get('/kelola-data-mahasiswa', [AdminProdiController::class, 'kelolaDataMahasiswa']);
        Route::post('/kelola-data-mahasiswa', [AdminProdiController::class, 'kelolaDataMahasiswa']);
        Route::put('/kelola-data-mahasiswa', [AdminProdiController::class, 'kelolaDataMahasiswa']);
        Route::delete('/kelola-data-mahasiswa', [AdminProdiController::class, 'kelolaDataMahasiswa']);
    });
    // Kelola data mata kuliah
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola data mata kuliah'])->group(function () {
        Route::get('/kelola-data-mata-kuliah', [AdminProdiController::class, 'kelolaDataMataKuliah']);
        Route::post('/kelola-data-mata-kuliah', [AdminProdiController::class, 'kelolaDataMataKuliah']);
        Route::put('/kelola-data-mata-kuliah', [AdminProdiController::class, 'kelolaDataMataKuliah']);
        Route::delete('/kelola-data-mata-kuliah', [AdminProdiController::class, 'kelolaDataMataKuliah']);
    });
    // Kelola data kelas
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola data kelas'])->group(function () {
        Route::get('/kelola-data-kelas', [AdminProdiController::class, 'kelolaDataKelas']);
        Route::post('/kelola-data-kelas', [AdminProdiController::class, 'kelolaDataKelas']);
        Route::put('/kelola-data-kelas', [AdminProdiController::class, 'kelolaDataKelas']);
        Route::delete('/kelola-data-kelas', [AdminProdiController::class, 'kelolaDataKelas']);
    });
    // Mendaftarkan mahasiswa ke kelas
    Route::middleware(['role:Admin Prodi', 'permission:Mendaftarkan mahasiswa ke kelas'])->group(function () {
        Route::get('/daftar-mahasiswa-ke-kelas', [AdminProdiController::class, 'daftarMahasiswaKeKelas']);
        Route::post('/daftar-mahasiswa-ke-kelas', [AdminProdiController::class, 'daftarMahasiswaKeKelas']);
        Route::put('/daftar-mahasiswa-ke-kelas', [AdminProdiController::class, 'daftarMahasiswaKeKelas']);
        Route::delete('/daftar-mahasiswa-ke-kelas', [AdminProdiController::class, 'daftarMahasiswaKeKelas']);
    });
    // Kelola data cpl
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola data CPL'])->group(function () {
        Route::get('/kelola-data-cpl', [AdminProdiController::class, 'kelolaDataCpl']);
        Route::post('/kelola-data-cpl', [AdminProdiController::class, 'kelolaDataCpl']);
        Route::put('/kelola-data-cpl', [AdminProdiController::class, 'kelolaDataCpl']);
        Route::delete('/kelola-data-cpl', [AdminProdiController::class, 'kelolaDataCpl']);
    });
    // Kelola data cpmk
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola data CPMK'])->group(function () {
        Route::get('/kelola-data-cpmk', [AdminProdiController::class, 'kelolaDataCpmk']);
        Route::post('/kelola-data-cpmk', [AdminProdiController::class, 'kelolaDataCpmk']);
        Route::put('/kelola-data-cpmk', [AdminProdiController::class, 'kelolaDataCpmk']);
        Route::delete('/kelola-data-cpmk', [AdminProdiController::class, 'kelolaDataCpmk']);
    });
    // Pemetaan cpl
    Route::middleware(['role:Admin Prodi', 'permission:Melakukan pemetaan CPL'])->group(function () {
        Route::get('/pemetaan-cpl', [AdminProdiController::class, 'pemetaanCpl']);
        Route::post('/pemetaan-cpl', [AdminProdiController::class, 'pemetaanCpl']);
        Route::put('/pemetaan-cpl', [AdminProdiController::class, 'pemetaanCpl']);
        Route::delete('/pemetaan-cpl', [AdminProdiController::class, 'pemetaanCpl']);
    });
    // Pemetaan cpmk
    Route::middleware(['role:Admin Prodi', 'permission:Melakukan pemetaan CPMK'])->group(function () {
        Route::get('/pemetaan-cpmk', [AdminProdiController::class, 'pemetaanCpmk']);
        Route::post('/pemetaan-cpmk', [AdminProdiController::class, 'pemetaanCpmk']);
        Route::put('/pemetaan-cpmk', [AdminProdiController::class, 'pemetaanCpmk']);
        Route::delete('/pemetaan-cpmk', [AdminProdiController::class, 'pemetaanCpmk']);
    });
    // Kelola sub penilaian
    Route::middleware(['role:Admin Prodi', 'permission:Mengelola sub penilaian'])->group(function () {
        Route::get('/data-jenis-penilaian-prodi', [AdminProdiController::class, 'dataJenisPenilaian']);
        Route::get('/kelola-sub-penilaian-oleh-prodi', [AdminProdiController::class, 'kelolaSubPenilaian']);
        Route::post('/kelola-sub-penilaian', [AdminProdiController::class, 'kelolaSubPenilaian']);
        Route::put('/kelola-sub-penilaian', [AdminProdiController::class, 'kelolaSubPenilaian']);
        Route::get('/kelas-cpl-cpmk', [AdminProdiController::class, 'kelasCplCpmk']);
        Route::delete('/kelola-sub-penilaian', [AdminProdiController::class, 'kelolaSubPenilaian']);
    });

    // Kaprodi
    Route::middleware(['role:Kaprodi', 'permission:Melihat hasil perhitungan'])->group(function () {
        Route::get('/status-pengisian-nilai', [KaprodiController::class, 'statusPengisianNilai']);
        Route::get('/nilai-cpl-seluruh-mata-kuliah', [KaprodiController::class, 'nilaiCplSeluruhMataKuliah']);
        Route::get('/daftar-mata-kuliah', [KaprodiController::class, 'melihatDaftarMataKuliah']);
        Route::get('/detail-perhitungan-perkelas', [KaprodiController::class, 'detailPerhitunganPerkelas']);
        // Route::post('/map-cpl', [PermissionController::class, 'mapCPL']);
        // Route::post('/map-cpmk', [PermissionController::class, 'mapCPMK']);
    });

    // Dosen
    // Kelola sub penilaian
    Route::middleware(['role:Dosen', 'permission:Mengelola sub penilaian'])->group(function () {
        Route::get('/data-jenis-penilaian', [DosenController::class, 'dataJenisPenilaian']);
        Route::get('/data-kelas-mata-kuliah', [DosenController::class, 'dataKelasMataKuliah']);
        Route::get('/kelola-sub-penilaian-oleh-dosen', [DosenController::class, 'kelolaSubPenilaian']);
        // Route::post('/kelola-sub-penilaian', [DosenController::class, 'kelolaSubPenilaian']);
        // Route::put('/kelola-sub-penilaian', [DosenController::class, 'kelolaSubPenilaian']);
        // Route::delete('/kelola-sub-penilaian', [DosenController::class, 'kelolaSubPenilaian']);
    });

    // Kelola nilai mahasiswa
    Route::middleware(['role:Dosen', 'permission:Mengelola data nilai mahasiswa'])->group(function () {
        Route::get('/kelola-nilai-mahasiswa', [DosenController::class, 'kelolaNilaiSubPenilaianMahasiswa']);
        Route::post('/kelola-nilai-mahasiswa', [DosenController::class, 'kelolaNilaiSubPenilaianMahasiswa']);
        Route::put('/kelola-nilai-mahasiswa', [DosenController::class, 'kelolaNilaiSubPenilaianMahasiswa']);
        Route::delete('/kelola-nilai-mahasiswa', [DosenController::class, 'kelolaNilaiSubPenilaianMahasiswa']);
    });
});
