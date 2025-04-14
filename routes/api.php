<?php

use App\Http\Controllers\AuthController;
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

// On progress
// Route::middleware(['auth:sanctum'])->group(function () {
//     // Admin Universitas
//     Route::middleware(['role:admin universitas'])->group(function () {
//         Route::post('/manage-admin-universitas', [PermissionController::class, 'manageAdminUniversitas']);
//         Route::post('/manage-admin-prodi', [PermissionController::class, 'manageAdminProdi']);
//         Route::get('/view-calculation-results', [PermissionController::class, 'viewCalculationResults']);
//     });

//     // Admin Prodi
//     Route::middleware(['role:admin prodi'])->group(function () {
//         Route::post('/manage-kaprodi', [PermissionController::class, 'manageKaprodi']);
//         Route::post('/manage-dosen', [PermissionController::class, 'manageDosen']);
//         Route::post('/manage-mahasiswa', [PermissionController::class, 'manageMahasiswa']);
//         Route::post('/manage-mata-kuliah', [PermissionController::class, 'manageMataKuliah']);
//         Route::post('/manage-cpl', [PermissionController::class, 'manageCPL']);
//         Route::post('/manage-cpmk', [PermissionController::class, 'manageCPMK']);
//         Route::get('/view-calculation-results', [PermissionController::class, 'viewCalculationResults']);
//     });

//     // Kaprodi
//     Route::middleware(['role:kaprodi'])->group(function () {
//         Route::post('/map-cpl', [PermissionController::class, 'mapCPL']);
//         Route::post('/map-cpmk', [PermissionController::class, 'mapCPMK']);
//     });

//     // Dosen
//     Route::middleware(['role:dosen'])->group(function () {
//         Route::post('/input-bobot-cpl', [PermissionController::class, 'inputBobotCPL']);
//         Route::post('/input-bobot-cpmk', [PermissionController::class, 'inputBobotCPMK']);
//         Route::post('/manage-nilai-mahasiswa', [PermissionController::class, 'manageNilaiMahasiswa']);
//     });
// });
