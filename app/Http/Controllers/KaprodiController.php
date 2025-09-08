<?php

namespace App\Http\Controllers;

use App\Http\Requests\MelihatHasilPerhitunganRequest;
use App\Services\KaprodiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KaprodiController extends Controller
{
    protected $kaprodiService;

    /**
     * Inject service yang dibutuhkan.
     *
     * @param KaprodiService $kaprodiService
     */
    public function __construct(KaprodiService $kaprodiService)
    {
        $this->kaprodiService = $kaprodiService;
    }

    public function melihatDaftarMataKuliah()
    {
        $result  = $this->kaprodiService->melihatDaftarMataKuliah();
        return response()->json($result, 200);
    }

    /**
     * Tampilkan “Hasil Perhitungan” lengkap untuk satu kelas.
     *
     * @param  int  $kelas_id
     * @return JsonResponse
     */
    public function detailPerhitunganPerkelas(MelihatHasilPerhitunganRequest $request): JsonResponse
    {
        $kelasId = $request->validated()['kelas_id'];

        $result = $this->kaprodiService->detailPerhitunganPerkelas($kelasId);

        return response()->json($result, 200);
    }

    public function melihatHasilPerhitungan(MelihatHasilPerhitunganRequest $request)
    {
        // Dapatkan data tervalidasi, lalu tambahkan key 'action' (misalnya dikirim via query atau body)
        $data = $request->validated();
        // Pastikan key 'action' diatur melalui request (misalnya store, update, view, delete)
        $data['action'] = $request->input('action');

        // $data['dosen_id'] = $request->user()->id;

        $result = $this->kaprodiService->melihatHasilPerhitungan($data);

        // Status code ditetapkan berdasarkan jenis aksi, 201 untuk store, 200 untuk view, update, delete
        $statusCode = in_array($data['action'], ['store']) ? 201 : 200;

        // Jika pesan tidak mengandung kata "berhasil" (artinya terjadi error), override status code menjadi 422
        if (stripos($result['message'], 'berhasil') === false) {
            $statusCode = 422;
        }

        return response()->json($result, $statusCode);
    }
}
