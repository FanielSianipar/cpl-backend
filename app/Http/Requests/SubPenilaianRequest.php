<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubPenilaianRequest extends FormRequest
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
     * Dapatkan aturan validasi yang berlaku untuk request sub-penilaian.
     */
    public function rules(): array
    {
        $rules = [
            'action' => 'required|in:store,update,view,delete',
        ];

        $action = $this->input('action');

        // Untuk store & update butuh data lengkap
        if ($action === 'store') {
            $rules += [
                'penilaian_id'        => ['required', 'exists:penilaian,penilaian_id'],
                'kelas_id'            => ['required', 'exists:kelas,kelas_id'],
                'nama_sub_penilaian'  => ['required', 'string', 'max:100'],
                'cpmks'               => ['required', 'array', 'min:1'],
                'cpmks.*.mata_kuliah_id' => ['required', 'exists:mata_kuliah,mata_kuliah_id'],
                'cpmks.*.cpmk_id'        => ['required', 'exists:cpmk,cpmk_id'],
                'cpmks.*.cpl_id'         => ['required', 'exists:cpl,cpl_id'],
                'cpmks.*.bobot'          => ['required', 'numeric', 'min:0'],
            ];
        } elseif ($action === 'update') {
            $rules += [
                'sub_penilaian_id'    => ['required', 'exists:sub_penilaian,sub_penilaian_id'],
                'penilaian_id'        => ['sometimes', 'exists:penilaian,penilaian_id'],
                'kelas_id'            => ['sometimes', 'exists:kelas,kelas_id'],
                'nama_sub_penilaian'  => ['sometimes', 'string', 'max:100'],
                'cpmks'               => ['sometimes', 'array', 'min:1'],
                'cpmks.*.mata_kuliah_id' => ['required_with:cpmks', 'exists:mata_kuliah,mata_kuliah_id'],
                'cpmks.*.cpmk_id'        => ['required_with:cpmks', 'exists:cpmk,cpmk_id'],
                'cpmks.*.cpl_id'         => ['required_with:cpmks', 'exists:cpl,cpl_id'],
                'cpmks.*.bobot'          => ['required_with:cpmks', 'numeric', 'min:0'],
            ];
        }
        // Untuk delete cukup sub_penilaian_id
        elseif ($action === 'delete') {
            $rules['sub_penilaian_id'] = ['required', 'exists:sub_penilaian,sub_penilaian_id'];
        }

        return $rules;
    }

    /**
     * Pesan‐pesan khusus untuk validasi.
     */
    public function messages(): array
    {
        return [
            'action.required'             => 'Action harus diisi (store, update, view, atau delete).',
            'action.in'                   => 'Action tidak valid, pilih: store, update, view, delete.',
            'penilaian_id.required'       => 'ID penilaian wajib diisi.',
            'penilaian_id.exists'         => 'Penilaian tidak ditemukan.',
            'kelas_id.required'           => 'ID kelas wajib diisi.',
            'kelas_id.exists'             => 'Kelas tidak ditemukan.',
            'nama_sub_penilaian.required'           => 'Nama sub-penilaian wajib diisi.',
            'nama_sub_penilaian.string'             => 'Nama sub-penilaian harus berupa teks.',
            'nama_sub_penilaian.max'                => 'Nama sub-penilaian maksimal 100 karakter.',
            'sub_penilaian_id.exists'     => 'Sub-penilaian tidak ditemukan.',
            'cpmks.required'              => 'Data CPMK-CPL wajib disertakan untuk aksi store/update.',
            'cpmks.array'                 => 'Data CPMK-CPL harus berupa array.',
            'cpmks.min'                   => 'Minimal satu data CPMK-CPL harus disertakan.',
            'cpmks.*.mata_kuliah_id.required' => 'ID mata kuliah untuk CPMK wajib diisi.',
            'cpmks.*.mata_kuliah_id.exists'   => 'Mata kuliah pada CPMK tidak valid.',
            'cpmks.*.cpmk_id.required'        => 'ID CPMK wajib diisi.',
            'cpmks.*.cpmk_id.exists'          => 'CPMK tidak valid.',
            'cpmks.*.cpl_id.required'         => 'ID CPL wajib diisi.',
            'cpmks.*.cpl_id.exists'           => 'CPL tidak valid.',
            'cpmks.*.bobot.required'          => 'Bobot CPMK wajib diisi.',
            'cpmks.*.bobot.numeric'           => 'Bobot CPMK harus berupa angka.',
            'cpmks.*.bobot.min'               => 'Bobot CPMK minimal 0.',
        ];
    }
}
