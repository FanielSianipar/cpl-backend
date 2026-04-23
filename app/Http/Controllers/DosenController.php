<?php

namespace App\Http\Controllers;

use App\Http\Requests\NilaiSubPenilaianMahasiswaRequest;
use App\Http\Requests\SubPenilaianRequest;
use App\Services\DosenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    protected $dosenService;

    /**
     * Inject service yang dibutuhkan.
     *
     * @param DosenService $dosenService
     */
    public function __construct(DosenService $dosenService)
    {
        $this->dosenService = $dosenService;
    }

    /**
     * Tampilkan keseluruhan data jenis Penilaian.
     *
     * @return JsonResponse
     */
    public function dataJenisPenilaian(): JsonResponse
    {
        $result = $this->dosenService->dataJenisPenilaian();

        return response()->json($result, 200);
    }

    /**
     * Tampilkan keseluruhan data kelas mata kuliah yang diampu oleh dosen.
     *
     * @return JsonResponse
     */
    public function dataKelasMataKuliah(): JsonResponse
    {
        $result = $this->dosenService->dataKelasMataKuliah();

        return response()->json($result, 200);
    }

    public function kelolaSubPenilaian(SubPenilaianRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $result = $this->dosenService->kelolaSubPenilaian($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        // Jika pesan tidak mengandung kata "berhasil" (artinya terjadi error), override status code menjadi 422
        if (stripos($result['message'], 'berhasil') === false) {
            $statusCode = 422;
        }

        return response()->json($result, $statusCode);
    }

    public function kelolaNilaiSubPenilaianMahasiswa(NilaiSubPenilaianMahasiswaRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        $data['dosen_id'] = $request->user()->id;

        $result = $this->dosenService->kelolaNilaiSubPenilaianMahasiswa($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        // Jika pesan tidak mengandung kata "berhasil" (artinya terjadi error), override status code menjadi 422
        if (stripos($result['message'], 'berhasil') === false) {
            $statusCode = 422;
        }

        return response()->json($result, $statusCode);
    }

    public function cplCpmk(Request $request, int $id)
    {
        $subId = $request->query('sub_penilaian_id');
        $result = $this->dosenService->listCplCpmkForKelas($id, $subId);
        $status = isset($result['data']) && !empty($result['data']['cpl_cpmk']) ? 200 : 200;
        return response()->json($result, $status);
    }
}
