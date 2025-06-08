<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CplRequest;
use App\Http\Requests\CpmkRequest;
use App\Http\Requests\MahasiswaRequest;
use App\Http\Requests\MataKuliahRequest;
use App\Http\Requests\PemetaanCplRequest;
use App\Http\Requests\StoreAkunRequest;
use App\Services\AdminProdiService;

class AdminProdiController extends Controller
{
    protected $adminProdiService;

    /**
     * Inject service yang dibutuhkan.
     *
     * @param AdminProdiService $adminProdiService
     */
    public function __construct(AdminProdiService $adminProdiService)
    {
        $this->adminProdiService = $adminProdiService;
    }

    /**
     * Mengelola akun Kaprodi (view, store, update, delete).
     *
     * Ekspektasi: Request mengandung parameter 'action' untuk menentukan operasi CRUD.
     *
     * @param StoreAkunRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaAkunKaprodi(StoreAkunRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminProdiService->kelolaAkunKaprodi($data);

        // Status code ditetapkan berdasarkan jenis aksi, misal 201 untuk store/update, 200 untuk view/delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Mengelola akun Dosen (view, store, update, delete).
     *
     * Ekspektasi: Request mengandung parameter 'action' untuk menentukan operasi CRUD.
     *
     * @param StoreAkunRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaAkunDosen(StoreAkunRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminProdiService->kelolaAkunDosen($data);

        // Status code ditetapkan berdasarkan jenis aksi, misal 201 untuk store/update, 200 untuk view/delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Endpoint untuk mengelola data Mahasiswa.
     * Untuk setiap operasi CRUD, parameter 'action' harus disertakan.
     *
     * @param \App\Http\Requests\MahasiswaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaDataMahasiswa(MahasiswaRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminProdiService->kelolaDataMahasiswa($data);

        // Status code ditetapkan berdasarkan jenis aksi, misal 201 untuk store/update, 200 untuk view/delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Endpoint untuk mengelola data Mata Kuliah.
     * Untuk setiap operasi CRUD, parameter 'action' harus disertakan.
     *
     * @param \App\Http\Requests\MataKuliahRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaDataMataKuliah(MataKuliahRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminProdiService->kelolaDataMataKuliah($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Endpoint untuk mengelola data CPL.
     * Untuk setiap operasi CRUD, parameter 'action' harus disertakan.
     *
     * @param \App\Http\Requests\CplRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaDataCpl(CplRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminProdiService->kelolaDataCpl($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Endpoint untuk mengelola data CPMK.
     * Untuk setiap operasi CRUD, parameter 'action' harus disertakan.
     *
     * @param \App\Http\Requests\CpmkRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelolaDataCpmk(CpmkRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminProdiService->kelolaDataCpmk($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    public function pemetaanCpl(PemetaanCplRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->adminProdiService->pemetaanCpl($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        // Jika pesan tidak mengandung kata "berhasil" (artinya terjadi error), override status code menjadi 422
        if (stripos($result['message'], 'berhasil') === false) {
            $statusCode = 422;
        }

        return response()->json($result, $statusCode);
    }
}
