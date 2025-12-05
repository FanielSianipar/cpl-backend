<?php

namespace App\Services;

use App\Models\SubPenilaian;
use App\Models\SubPenilaianCpmkMataKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubPenilaianService
{
    /**
     * Mengelola operasi sub_penilaian beserta pivot bobot ke CPMK–CPL.
     *
     * Format input $data:
     * [
     *   'action'            => 'view'|'store'|'update'|'delete',
     *   // untuk view & delete:
     *   'sub_penilaian_id'  => (int),
     *   // untuk store & update:
     *   'penilaian_id'      => (int),
     *   'kelas_id'          => (int),
     *   'nama_sub_penilaian'          => (string),
     *   'cpmks'              => [
     *       ['mata_kuliah_id'=>int,'cpmk_id'=>int,'cpl_id'=>int,'bobot'=>float],
     *       …
     *   ]
     * ]
     *
     * @param  array  $data
     * @return array
     */
    // public function kelolaSubPenilaian(array $data): array
    // {
    //     if (!isset($data['action'])) {
    //         return ['message' => 'Action tidak ditemukan.'];
    //     }

    //     switch ($data['action']) {
    //         case 'view':
    //             if (isset($data['sub_penilaian_id'])) {
    //                 $subPenilaian = SubPenilaian::with('cpmks')->find($data['sub_penilaian_id']);

    //                 if (!$subPenilaian) {
    //                     return ['message' => 'Sub-penilaian tidak ditemukan.'];
    //                 }

    //                 return [
    //                     'data'    => $subPenilaian,
    //                     'message' => 'Data sub-penilaian berhasil diambil.'
    //                 ];
    //             }

    //             if (!isset($data['kelas_id'])) {
    //                 return ['message' => 'Parameter kelas_id diperlukan untuk mengambil semua data sub-penilaian.'];
    //             }
    //             $all = SubPenilaian::with('cpmks')->where('kelas_id', $data['kelas_id'])->get();
    //             return [
    //                 'data'    => $all,
    //                 'message' => 'Semua data sub-penilaian di kelas ini berhasil diambil.'
    //             ];

    //         case 'store':
    //             return $this->syncSubPenilaianCpmk($data, 'store');

    //         case 'update':
    //             return $this->syncSubPenilaianCpmk($data, 'update');

    //         case 'delete':
    //             if (!isset($data['sub_penilaian_id'])) {
    //                 return ['message' => 'ID sub-penilaian diperlukan untuk delete.'];
    //             }

    //             DB::beginTransaction();

    //             // Hapus data pivot
    //             SubPenilaianCpmkMataKuliah::where('sub_penilaian_id', $data['sub_penilaian_id'])->delete();

    //             // Hapus sub_penilaian
    //             SubPenilaian::findOrFail($data['sub_penilaian_id'])->delete();

    //             DB::commit();

    //             return ['message' => 'Sub-penilaian berhasil dihapus.'];

    //         default:
    //             return ['message' => 'Action tidak dikenali.'];
    //     }
    // }

    public function kelolaSubPenilaian(array $data): array
    {
        if (!isset($data['action'])) {
            return ['message' => 'Action tidak ditemukan.'];
        }

        switch ($data['action']) {
            case 'view':
                // view single master sub_penilaian (tanpa cpmks/pivot)
                if (isset($data['sub_penilaian_id'])) {
                    $subPenilaian = SubPenilaian::find($data['sub_penilaian_id']);
                    if (!$subPenilaian) {
                        return ['message' => 'Sub-penilaian tidak ditemukan.'];
                    }
                    return [
                        'data' => $subPenilaian,
                        'message' => 'Data sub-penilaian berhasil diambil.'
                    ];
                }

                // view list per kelas (hanya master records)
                if (!isset($data['kelas_id'])) {
                    return ['message' => 'Parameter kelas_id diperlukan untuk mengambil semua data sub-penilaian.'];
                }

                $subPenilaians = SubPenilaian::where('kelas_id', $data['kelas_id'])
                    ->orderBy('created_at', 'asc')
                    ->get(['sub_penilaian_id', 'penilaian_id', 'kelas_id', 'nama_sub_penilaian', 'created_at', 'updated_at']);

                return [
                    'data' => $subPenilaians,
                    'message' => 'Semua data sub-penilaian di kelas ini berhasil diambil.'
                ];

            case 'store':
                // langsung lakukan create master sub_penilaian di sini (tanpa pivot)
                if (!isset($data['penilaian_id'], $data['kelas_id'], $data['nama_sub_penilaian'])) {
                    return ['message' => 'Data sub-penilaian tidak lengkap.'];
                }

                try {
                    $subPenilaian = SubPenilaian::create([
                        'penilaian_id'       => $data['penilaian_id'],
                        'kelas_id'           => $data['kelas_id'],
                        'nama_sub_penilaian' => $data['nama_sub_penilaian'],
                    ]);

                    return [
                        'data'    => $subPenilaian,
                        'message' => 'Sub-penilaian berhasil ditambahkan.'
                    ];
                } catch (\Exception $e) {
                    return ['message' => 'Gagal menyimpan sub-penilaian: ' . $e->getMessage()];
                }

            case 'delete':
                if (!isset($data['sub_penilaian_id'])) {
                    return ['message' => 'ID sub-penilaian diperlukan untuk delete.'];
                }

                DB::beginTransaction();
                try {
                    SubPenilaian::findOrFail($data['sub_penilaian_id'])->delete();
                    // jika FK cascade tidak aktif, uncomment baris berikut:
                    // SubPenilaianCpmkMataKuliah::where('sub_penilaian_id', $data['sub_penilaian_id'])->delete();
                    DB::commit();
                    return ['message' => 'Sub-penilaian berhasil dihapus.'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    return ['message' => 'Gagal menghapus sub-penilaian: ' . $e->getMessage()];
                }

            default:
                return ['message' => 'Action tidak dikenali.'];
        }
    }

    /**
     * Store or Update sub_penilaian + pivot bobot CPMK–CPL.
     */
    protected function syncSubPenilaianCpmk(array $data, string $mode): array
    {
        // validasi minimal payload
        if (
            !isset(
                $data['penilaian_id'],
                $data['kelas_id'],
                $data['nama_sub_penilaian'],
                $data['cpmks']
            ) || !is_array($data['cpmks'])
        ) {
            return ['message' => 'Data sub-penilaian atau pivot CPMK tidak lengkap.'];
        }

        try {
            DB::beginTransaction();

            // 1) Create atau update master SubPenilaian
            if ($mode === 'store') {
                $subPenilaian = SubPenilaian::create([
                    'penilaian_id'        => $data['penilaian_id'],
                    'kelas_id'            => $data['kelas_id'],
                    'nama_sub_penilaian'  => $data['nama_sub_penilaian'],
                ]);
            } else {
                if (!isset($data['sub_penilaian_id'])) {
                    throw new \Exception('sub_penilaian_id diperlukan untuk update.');
                }
                $subPenilaian = SubPenilaian::findOrFail($data['sub_penilaian_id']);
                $subPenilaian->update([
                    'nama_sub_penilaian' => $data['nama_sub_penilaian'],
                ]);

                // hapus pivot lama
                SubPenilaianCpmkMataKuliah::where('sub_penilaian_id', $subPenilaian->sub_penilaian_id)
                    ->delete();
            }

            // 2) Prepare bulk insert pivot
            $now       = now();
            $insertArr = [];

            foreach ($data['cpmks'] as $row) {
                if (!isset($row['cpmk_id'], $row['bobot'])) {
                    throw ValidationException::withMessages([
                        'cpmks' => 'Data CPMK tidak lengkap.'
                    ]);
                }

                $insertArr[] = [
                    'sub_penilaian_id' => $subPenilaian->sub_penilaian_id,
                    'cpmk_id'          => $row['cpmk_id'],
                    'bobot'            => $row['bobot'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }

            // 3) Insert pivot
            SubPenilaianCpmkMataKuliah::insert($insertArr);

            DB::commit();

            // kembalikan data lengkap
            $subPenilaian->load('cpmks');
            return [
                'data'    => $subPenilaian,
                'message' => 'Sub-penilaian berhasil ' . ($mode === 'store' ? 'ditambahkan.' : 'diperbarui.')
            ];
        } catch (ValidationException $ve) {
            DB::rollBack();
            $payload = $ve->getResponse()->getData();
            return ['message' => $payload->message ?? $ve->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['message' => 'Gagal simpan sub-penilaian: ' . $e->getMessage()];
        }
    }
}
