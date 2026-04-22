<?php

namespace App\Services;

use App\Models\CPMK;
use App\Models\Kelas;
use App\Models\SubPenilaian;
use App\Models\SubPenilaianCpmkMataKuliah;
use Illuminate\Support\Facades\DB;

class SubPenilaianService
{
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

            case 'store_bobot':
                // payload: kelas_id, rows: [ { cpmk_id, sub-penilaian: [{sub_penilaian_id,bobot}, ...] }, ... ]
                if (!isset($data['kelas_id'], $data['rows']) || !is_array($data['rows'])) {
                    return ['message' => 'Parameter kelas_id dan rows (array) diperlukan.'];
                }

                $kelasId = (int) $data['kelas_id'];
                $rows = $data['rows'];

                // ambil semua sub_penilaian yang valid untuk kelas ini
                $validSubIds = SubPenilaian::where('kelas_id', $kelasId)->pluck('sub_penilaian_id')->toArray();
                if (empty($validSubIds)) {
                    return ['message' => 'Tidak ada sub-penilaian untuk kelas ini.'];
                }

                // ambil mata_kuliah_id dari kelas untuk cek bobot_acuan di DB
                $kelas = Kelas::find($kelasId);
                if (!$kelas) {
                    return ['message' => 'Kelas tidak ditemukan.'];
                }
                $mataKuliahId = $kelas->mata_kuliah_id;

                $errors = [];
                $upserts = []; // kumpulan pasangan untuk upsert jika valid
                $cpmkIds = [];

                // kumpulkan semua cpmk_id dari rows untuk ambil bobot_acuan sekaligus
                foreach ($rows as $r) {
                    if (isset($r['cpmk_id'])) {
                        $cpmkIds[] = (int)$r['cpmk_id'];
                    }
                }
                $cpmkIds = array_values(array_unique($cpmkIds));

                // ambil bobot_acuan untuk semua cpmk yang relevan
                $bobotAcuanMap = [];

                if (!empty($cpmkIds)) {
                    $rowsWithAcuan = DB::table('cpmk')
                        ->join('cpmk_mata_kuliah', 'cpmk.cpmk_id', '=', 'cpmk_mata_kuliah.cpmk_id')
                        ->where('cpmk.mata_kuliah_id', $mataKuliahId)
                        ->whereIn('cpmk.cpmk_id', $cpmkIds)
                        ->select('cpmk.cpmk_id', 'cpmk_mata_kuliah.bobot as bobot_acuan')
                        ->get();

                    foreach ($rowsWithAcuan as $r) {
                        $bobotAcuanMap[(int)$r->cpmk_id] = (float)$r->bobot_acuan;
                    }
                }

                // Validasi tiap baris (per CPMK)
                foreach ($rows as $idx => $row) {
                    if (!isset($row['cpmk_id'])) {
                        $errors[] = "Baris ke-{$idx} format tidak valid: cpmk_id diperlukan.";
                        continue;
                    }

                    $cpmkId = (int)$row['cpmk_id'];

                    // Hanya dukung key 'sub-penilaian'
                    $subList = $row['sub-penilaian'] ?? null;
                    if (!is_array($subList) || empty($subList)) {
                        $errors[] = "Baris CPMK {$cpmkId}: sub-penilaian harus berupa array dan tidak kosong.";
                        continue;
                    }

                    // hitung total bobot yang dikirim untuk baris ini
                    $rowTotal = 0.0;
                    $invalidSubs = [];
                    foreach ($subList as $a) {
                        if (!isset($a['sub_penilaian_id'], $a['bobot'])) {
                            $errors[] = "Baris CPMK {$cpmkId}: setiap sub-penilaian harus berisi sub_penilaian_id dan bobot.";
                            $invalidSubs = null; // tanda error struktur
                            break;
                        }
                        $sid = (int)$a['sub_penilaian_id'];
                        $b = (float)$a['bobot'];

                        // pastikan sub milik kelas
                        if (!in_array($sid, $validSubIds, true)) {
                            $invalidSubs[] = $sid;
                        } else {
                            $rowTotal += $b;
                            $upserts[] = ['sub_penilaian_id' => $sid, 'cpmk_id' => $cpmkId, 'bobot' => $b];
                        }
                    }

                    if ($invalidSubs === null) {
                        // sudah ada error struktur, lanjut ke baris berikutnya
                        continue;
                    }

                    if (!empty($invalidSubs)) {
                        $errors[] = "Baris CPMK {$cpmkId}: sub_penilaian tidak terhubung ke kelas ini: " . implode(',', array_unique($invalidSubs));
                        continue;
                    }

                    // ambil bobot_acuan dari map (diambil dari DB sebelumnya)
                    $bobotAcuan = $bobotAcuanMap[$cpmkId] ?? null;

                    if ($bobotAcuan === null) {
                        // jika tidak ada di map, berarti pivot tidak punya bobot_acuan untuk cpmk ini
                        $cpmkKode = CPMK::find($cpmkId)->kode_cpmk ?? $cpmkId;
                        $errors[] = "Baris CPMK {$cpmkKode}: bobot acuan tidak ditemukan untuk mata kuliah ini.";
                        continue;
                    }

                    // validasi total per baris harus sama dengan bobot_acuan
                    if (abs($rowTotal - $bobotAcuan) > 0.0001) {
                        $errors[] = "Baris CPMK {$cpmkId}: total bobot sub-penilaian ({$rowTotal}) tidak sama dengan bobot acuan ({$bobotAcuan}).";
                        continue;
                    }
                }

                if (!empty($errors)) {
                    return ['message' => 'Validasi gagal: ' . implode(' | ', $errors)];
                }

                // Semua valid -> lakukan upsert dalam transaksi
                DB::beginTransaction();
                try {
                    foreach ($upserts as $u) {
                        SubPenilaianCpmkMataKuliah::updateOrCreate(
                            [
                                'sub_penilaian_id' => $u['sub_penilaian_id'],
                                'cpmk_id' => $u['cpmk_id']
                            ],
                            [
                                'bobot' => $u['bobot']
                            ]
                        );
                    }
                    DB::commit();
                    return ['message' => 'Bobot sub-penilaian berhasil disimpan.'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    return ['message' => 'Gagal menyimpan bobot sub-penilaian: ' . $e->getMessage()];
                }

            default:
                return ['message' => 'Action tidak dikenali.'];
        }
    }
}
