<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAkunRequest;
use App\Services\AdminUniversitasService;
use Illuminate\Http\Request;

class AdminUniversitasController extends Controller
{
    protected $adminUniversitasService;

    /**
     * Inject service yang dibutuhkan.
     *
     * @param AdminUniversitasService $adminUniversitasService
     */
    public function __construct(AdminUniversitasService $adminUniversitasService)
    {
        $this->adminUniversitasService = $adminUniversitasService;
    }

    /**
     * Mengelola akun Admin Universitas (view, store, update, delete).
     *
     * Ekspektasi: Request mengandung parameter 'action' untuk menentukan operasi CRUD.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAkunAdminUniversitas(Request $request)
    {
        try {
            $data = array_merge($request->all(), ['action' => 'view']);
            $result = $this->adminUniversitasService->kelolaAkunAdminUniversitas($data);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Method untuk membuat atau memperbarui akun Admin Universitas.
    public function storeAkunAdminUniversitas(StoreAkunRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->adminUniversitasService->kelolaAkunAdminUniversitas($data);

            // Sesuaikan status response: 201 untuk store/update, 200 untuk view/delete
            $status = in_array($data['action'], ['store']) ? 201 : 200;
            return response()->json($result, $status);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengelola akun Admin Prodi (view, store, update, delete).
     *
     * Ekspektasi: Request mengandung parameter 'action' untuk menentukan operasi CRUD.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function manageAdminProdi(Request $request)
    // {
    //     try {
    //         $data = $request->all();
    //         $result = $this->adminProdiService->kelolaAdminProdi($data);
    //         $status = (isset($data['action']) && $data['action'] === 'store') ? 201 : 200;

    //         return response()->json($result, $status);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Menampilkan hasil perhitungan.
     *
     * Contoh: Mengembalikan data dummy sebagai hasil perhitungan.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewCalculationResults()
    {
        try {
            // Implementasikan logika untuk memperoleh hasil perhitungan secara dinamis
            $results = [
                'total_students' => 100,
                'average_score'  => 85,
                // Data perhitungan lainnya dapat ditambahkan di sini
            ];

            return response()->json([
                'data'    => $results,
                'message' => 'Calculation results retrieved successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
