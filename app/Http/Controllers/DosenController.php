<?php

namespace App\Http\Controllers;

use App\Http\Requests\NilaiSubPenilaianMahasiswaRequest;
use App\Services\DosenService;

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
}
