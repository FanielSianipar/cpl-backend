<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NilaiSubPenilaianMahasiswaRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        // Anda bisa tambahkan cek permission/role di sini
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request nilai sub-penilaian mahasiswa.
     */
    public function rules(): array
    {
        $rules = [
            'action' => 'required|in:view,store,update,delete',
        ];

        $action = $this->input('action');

        if ($action === 'view') {
            // view: butuh kelas + pivot sub-penilaian–CPMK, mahasiswa_id opsional untuk filter detail
            $rules += [
                'kelas_id'                              => ['required', 'exists:kelas,kelas_id'],
                'sub_penilaian_cpmk_mata_kuliah_id'     => ['required', 'exists:sub_penilaian_cpmk_mata_kuliah,sub_penilaian_cpmk_mata_kuliah_id'],
                'mahasiswa_id'                          => ['sometimes', 'exists:mahasiswa,mahasiswa_id'],
            ];
        } elseif (in_array($action, ['store', 'update'], true)) {
            // store & update: semua field wajib
            $rules += [
                'kelas_id'                              => ['required', 'exists:kelas,kelas_id'],
                'sub_penilaian_cpmk_mata_kuliah_id'     => ['required', 'exists:sub_penilaian_cpmk_mata_kuliah,sub_penilaian_cpmk_mata_kuliah_id'],
                'mahasiswa_id'                          => ['required', 'exists:mahasiswa,mahasiswa_id'],
                'nilai_mentah'                          => ['required', 'numeric', 'min:0'],
            ];
        } elseif ($action === 'delete') {
            // delete: butuh semua key utama
            $rules += [
                'kelas_id'                              => ['required', 'exists:kelas,kelas_id'],
                'sub_penilaian_cpmk_mata_kuliah_id'     => ['required', 'exists:sub_penilaian_cpmk_mata_kuliah,sub_penilaian_cpmk_mata_kuliah_id'],
                'mahasiswa_id'                          => ['required', 'exists:mahasiswa,mahasiswa_id'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'action.required'                                 => 'Action harus diisi (view, store, update, atau delete).',
            'action.in'                                       => 'Action tidak valid, pilih: view, store, update, delete.',

            // view
            'kelas_id.required'                               => 'ID kelas wajib diisi.',
            'kelas_id.exists'                                 => 'Kelas tidak ditemukan.',
            'sub_penilaian_cpmk_mata_kuliah_id.required'      => 'ID sub-penilaian ↔ CPMK wajib diisi.',
            'sub_penilaian_cpmk_mata_kuliah_id.exists'        => 'Mapping sub-penilaian ↔ CPMK tidak ditemukan.',
            'mahasiswa_id.exists'                             => 'Mahasiswa tidak ditemukan.',

            // store & update
            'mahasiswa_id.required'                           => 'ID mahasiswa wajib diisi.',
            'nilai_mentah.required'                           => 'Nilai mentah wajib diisi.',
            'nilai_mentah.numeric'                            => 'Nilai mentah harus berupa angka.',
            'nilai_mentah.min'                                => 'Nilai mentah minimal 0.',

            // delete
            'mahasiswa_id.required'                           => 'ID mahasiswa wajib diisi untuk aksi delete.',
        ];
    }
}
