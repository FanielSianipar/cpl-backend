<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdiRequest;
use App\Http\Requests\StoreAkunRequest;
use App\Services\AdminUniversitasService;
use Illuminate\Http\JsonResponse;

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
     * Tampilkan keseluruhan data Fakultas.
     *
     * @return JsonResponse
     */
    public function dataFakultas(): JsonResponse
    {
        $result = $this->adminUniversitasService->dataFakultas();

        return response()->json($result, 200);
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

        // Status code ditetapkan berdasarkan jenis aksi, misal 201 untuk store, 200 untuk view, update, delete
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
    public function kelolaAkunAdminProdi(StoreAkunRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminUniversitasService->kelolaAkunAdminProdi($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
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
