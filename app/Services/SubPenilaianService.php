<?php

namespace App\Services;

use App\Models\MataKuliahCpmkPivot;
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
    public function kelolaSubPenilaian(array $data): array
    {
        if (!isset($data['action'])) {
            return ['message' => 'Action tidak ditemukan.'];
        }

        switch ($data['action']) {
            case 'view':
                if (isset($data['sub_penilaian_id'])) {
                    $subPenilaian = SubPenilaian::with('cpmks')->find($data['sub_penilaian_id']);

                    if (!$subPenilaian) {
                        return ['message' => 'Sub-penilaian tidak ditemukan.'];
                    }

                    return [
                        'data'    => $subPenilaian,
                        'message' => 'Data sub-penilaian berhasil diambil.'
                    ];
                }

                $all = SubPenilaian::with('cpmks')->get();
                return [
                    'data'    => $all,
                    'message' => 'Semua data sub-penilaian berhasil diambil.'
                ];

            case 'store':
                return $this->syncSubPenilaianCpmk($data, 'store');

            case 'update':
                return $this->syncSubPenilaianCpmk($data, 'update');

            case 'delete':
                if (!isset($data['sub_penilaian_id'])) {
                    return ['message' => 'ID sub-penilaian diperlukan untuk delete.'];
                }

                DB::beginTransaction();

                // Hapus data pivot
                SubPenilaianCpmkMataKuliah::where('sub_penilaian_id', $data['sub_penilaian_id'])->delete();

                // Hapus sub_penilaian
                SubPenilaian::findOrFail($data['sub_penilaian_id'])->delete();

                DB::commit();

                return ['message' => 'Sub-penilaian berhasil dihapus.'];

            default:
                return ['message' => 'Action tidak dikenali.'];
        }
    }

    /**
     * Store or Update sub_penilaian + pivot bobot CPMK–CPL.
     */
    protected function syncSubPenilaianCpmk(array $data, string $mode): array
    {
        // … validasi basic seperti sebelumnya …

        // 0) Siapkan master mapping: key = "mkId-cpmkId-cplId"
        $masterMap = MataKuliahCpmkPivot::get()
            ->keyBy(fn($m) => "{$m->mata_kuliah_id}-{$m->cpmk_id}-{$m->cpl_id}");

        // 1) Hitung grup incoming
        $incoming = [];
        foreach ($data['cpmks'] as $r) {
            $key = "{$r['mata_kuliah_id']}-{$r['cpmk_id']}-{$r['cpl_id']}";
            $incoming[$key] = ($incoming[$key] ?? 0) + $r['bobot'];
        }

        // 2) Validasi per grup
        foreach ($incoming as $key => $sumNew) {
            if (! isset($masterMap[$key])) {
                throw ValidationException::withMessages([
                    'cpmks' => "Mapping CPMK/CPL [$key] tidak ada di master."
                ]);
            }
            $masterBobot = $masterMap[$key]->bobot;

            // hitung existing total (kecuali sub_penilaian ini bila mode=update)
            $query = SubPenilaianCpmkMataKuliah::whereRaw("
            mata_kuliah_id = ?
            and cpmk_id = ?
            and cpl_id = ?
        ", explode('-', $key));

            if ($mode === 'update') {
                $query->where('sub_penilaian_id', '<>', $data['sub_penilaian_id']);
            }
            $existingSum = (float) $query->sum('bobot');

            if ($existingSum + $sumNew > $masterBobot) {
                throw ValidationException::withMessages([
                    'cpmks' => "Total bobot untuk [$key] melebihi batas master ({$masterBobot})."
                ]);
            }
        }

        // 3) Kalau lolos validasi, lanjut INSERT seperti biasa…
        // DB::beginTransaction();
        // try {
        //     // … create/update master sub_penilaian …
        //     // … bulk insert pivot …
        //     DB::commit();
        //     // …
        // } catch (ValidationException $ve) {
        //     DB::rollBack();
        //     return ['message' => array_values($ve->errors())[0][0]];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return ['message' => 'Gagal simpan: ' . $e->getMessage()];
        // }

        // validasi basic
        if (
            ! isset($data['penilaian_id'], $data['kelas_id'], $data['nama_sub_penilaian'], $data['cpmks'])
            || ! is_array($data['cpmks'])
        ) {
            return ['message' => 'Data sub-penilaian atau pivot CPMK tidak lengkap.'];
        }

        try {
            DB::beginTransaction();

            // 1) Create atau update master sub_penilaian
            if ($mode === 'store') {
                $subPenilaian = SubPenilaian::create([
                    'penilaian_id' => $data['penilaian_id'],
                    'kelas_id'     => $data['kelas_id'],
                    'nama_sub_penilaian'     => $data['nama_sub_penilaian'],
                ]);
            } else {
                if (! isset($data['sub_penilaian_id'])) {
                    throw new \Exception('sub_penilaian_id diperlukan untuk update.');
                }
                $subPenilaian = SubPenilaian::findOrFail($data['sub_penilaian_id']);
                $subPenilaian->update(['nama_sub_penilaian' => $data['nama_sub_penilaian']]);

                // hapus pivot lama
                SubPenilaianCpmkMataKuliah::where('sub_penilaian_id', $subPenilaian->sub_penilaian_id)
                    ->delete();
            }

            // 2) Prepare bulk insert pivot
            $now       = now();
            $insertArr = [];
            foreach ($data['cpmks'] as $row) {
                if (! isset($row['mata_kuliah_id'], $row['cpmk_id'], $row['cpl_id'], $row['bobot'])) {
                    throw new ValidationException(
                        validator: null,
                        response: response()->json(['message' => 'Data CPMK–CPL tidak lengkap.'], 422)
                    );
                }

                // (opsional) tambahkan validasi bobot vs master pivot di cpmk_mata_kuliah

                $insertArr[] = [
                    'sub_penilaian_id' => $subPenilaian->sub_penilaian_id,
                    'mata_kuliah_id'   => $row['mata_kuliah_id'],
                    'cpmk_id'          => $row['cpmk_id'],
                    'cpl_id'           => $row['cpl_id'],
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
            // response 422 dengan message custom
            $payload = $ve->getResponse()->getData();
            return ['message' => $payload->message ?? $ve->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['message' => 'Gagal simpan sub-penilaian: ' . $e->getMessage()];
        }
    }
}
