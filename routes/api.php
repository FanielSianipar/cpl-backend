<?php

use App\Http\Controllers\AdminUniversitasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
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
        Route::post('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
        Route::get('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
        Route::put('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
        Route::delete('/kelola-data-prodi', [AdminUniversitasController::class, 'kelolaDataProdi']);
    });
    // Kelola akun admin universitas
    Route::middleware(['role:Admin Universitas', 'permission:Mengelola akun admin universitas'])->group(function () {
        Route::get('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'viewAkunAdminUniversitas']);
        Route::post('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'storeAkunAdminUniversitas']);
        Route::put('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'storeAkunAdminUniversitas']);
        Route::delete('/kelola-akun-admin-universitas', [AdminUniversitasController::class, 'storeAkunAdminUniversitas']);
    });
    // Kelola akun admin prodi
    Route::middleware(['role:Admin Universitas', 'permission:Mengelola akun admin prodi'])->group(function () {
        Route::get('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'viewAkunAdminProdi']);
        Route::post('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'storeAkunAdminProdi']);
        Route::put('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'storeAkunAdminProdi']);
        Route::delete('/kelola-akun-admin-prodi', [AdminUniversitasController::class, 'storeAkunAdminProdi']);
    });
    // Lihat hasil perhitungan
    Route::middleware(['permission:view calculation results'])->group(function () {
        Route::get('/lihat-hasil-perhitungan', [AdminUniversitasController::class, 'viewCalculationResults']);
    });

    // Hanya Admin Prodi yang memiliki permission 'kelola admin prodi'
    Route::middleware(['role:admin prodi', 'permission:kelola admin prodi'])->group(function () {
        Route::get('/kelola-admin-prodi', [AdminUniversitasController::class, 'viewAkunAdminUniversitas']);
    });

    // Admin Prodi
    // Route::middleware(['role:admin prodi'])->group(function () {
    //     Route::post('/manage-kaprodi', [PermissionController::class, 'manageKaprodi']);
    //     Route::post('/manage-dosen', [PermissionController::class, 'manageDosen']);
    //     Route::post('/manage-mahasiswa', [PermissionController::class, 'manageMahasiswa']);
    //     Route::post('/manage-mata-kuliah', [PermissionController::class, 'manageMataKuliah']);
    //     Route::post('/manage-cpl', [PermissionController::class, 'manageCPL']);
    //     Route::post('/manage-cpmk', [PermissionController::class, 'manageCPMK']);
    //     Route::get('/view-calculation-results', [PermissionController::class, 'viewCalculationResults']);
    // });

    // // Kaprodi
    // Route::middleware(['role:kaprodi'])->group(function () {
    //     Route::post('/map-cpl', [PermissionController::class, 'mapCPL']);
    //     Route::post('/map-cpmk', [PermissionController::class, 'mapCPMK']);
    // });

    // // Dosen
    // Route::middleware(['role:dosen'])->group(function () {
    //     Route::post('/input-bobot-cpl', [PermissionController::class, 'inputBobotCPL']);
    //     Route::post('/input-bobot-cpmk', [PermissionController::class, 'inputBobotCPMK']);
    //     Route::post('/manage-nilai-mahasiswa', [PermissionController::class, 'manageNilaiMahasiswa']);
    // });
});
