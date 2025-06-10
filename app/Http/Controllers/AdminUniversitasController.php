<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdiRequest;
use App\Http\Requests\StoreAkunRequest;
use App\Services\AdminUniversitasService;

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
     * Endpoint untuk mengelola data Prodi.
     * Untuk setiap operasi CRUD, parameter 'action' harus disertakan.
     *
     * @param \App\Http\Requests\ProdiRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaDataProdi(ProdiRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminUniversitasService->kelolaDataProdi($data);

        // Status code ditetapkan berdasarkan jenis aksi, misal 201 untuk store/update, 200 untuk view/delete
        $statusCode = in_array($data['action'], ['store', 'update']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Mengelola akun Admin Universitas (view, store, update, delete).
     *
     * Ekspektasi: Request mengandung parameter 'action' untuk menentukan operasi CRUD.
     *
     * @param StoreAkunRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaAkunAdminUniversitas(StoreAkunRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminUniversitasService->kelolaAkunAdminUniversitas($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Mengelola akun Admin Prodi (view, store, update, delete).
     *
     * Ekspektasi: Request mengandung parameter 'action' untuk menentukan operasi CRUD.
     *
     * @param StoreAkunRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAkunAdminProdi(StoreAkunRequest $request)
    {
        try {
            $data = array_merge($request->all(), ['action' => 'view']);
            $result = $this->adminUniversitasService->kelolaAkunAdminProdi($data);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeAkunAdminProdi(StoreAkunRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->adminUniversitasService->kelolaAkunAdminProdi($data);

            // Sesuaikan status response: 201 untuk store, 200 untuk view, update, delete
            $status = in_array($data['action'], ['store']) ? 201 : 200;
            return response()->json($result, $status);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

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
