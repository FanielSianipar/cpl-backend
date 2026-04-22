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

    public function rules(): array
    {
        $rules = [
            'action' => 'required|in:store,view,delete,store_bobot',
        ];

        $action = $this->input('action');

        if ($action === 'view') {
            $rules += [
                'sub_penilaian_id' => [
                    'sometimes',
                    'required_without:kelas_id',
                    'exists:sub_penilaian,sub_penilaian_id'
                ],
                'kelas_id' => [
                    'sometimes',
                    'required_without:sub_penilaian_id',
                    'exists:kelas,kelas_id'
                ],
            ];
        } elseif ($action === 'store') {
            $rules += [
                'penilaian_id'       => ['required', 'exists:penilaian,penilaian_id'],
                'kelas_id'           => ['required', 'exists:kelas,kelas_id'],
                'nama_sub_penilaian' => ['required', 'string', 'max:100'],
            ];
        } elseif ($action === 'delete') {
            $rules['sub_penilaian_id'] = ['required', 'exists:sub_penilaian,sub_penilaian_id'];
        } elseif ($action === 'store_bobot') {
            // payload: kelas_id + rows[]
            $rules += [
                'kelas_id' => ['required', 'exists:kelas,kelas_id'],
                'rows' => ['required', 'array', 'min:1'],
                'rows.*.cpmk_id' => ['required', 'exists:cpmk,cpmk_id'],
                'rows.*.bobot_acuan' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'rows.*.sub-penilaian' => ['required', 'array'],
                'rows.*.sub-penilaian.*.sub_penilaian_id' => ['required', 'exists:sub_penilaian,sub_penilaian_id'],
                'rows.*.sub-penilaian.*.bobot' => ['required', 'numeric', 'min:0'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Action harus diisi (store, view, delete, atau store_bobot).',
            'action.in'       => 'Action tidak valid, pilih: store, view, delete, store_bobot.',

            // view
            'sub_penilaian_id.required_without' => 'ID sub-penilaian wajib diisi jika kelas_id kosong.',
            'sub_penilaian_id.exists'           => 'Sub-penilaian tidak ditemukan.',
            'kelas_id.required_without'         => 'ID kelas wajib diisi jika sub_penilaian_id kosong.',
            'kelas_id.exists'                   => 'Kelas tidak ditemukan.',

            // store (master)
            'penilaian_id.required'             => 'ID penilaian wajib diisi.',
            'penilaian_id.exists'               => 'Penilaian tidak ditemukan.',
            'kelas_id.required'                 => 'ID kelas wajib diisi.',
            'kelas_id.exists'                   => 'Kelas tidak ditemukan.',
            'nama_sub_penilaian.required'       => 'Nama sub-penilaian wajib diisi.',
            'nama_sub_penilaian.string'         => 'Nama sub-penilaian harus berupa teks.',
            'nama_sub_penilaian.max'            => 'Nama sub-penilaian maksimal 100 karakter.',

            // delete
            'sub_penilaian_id.required'         => 'ID sub-penilaian diperlukan untuk aksi delete.',
            'sub_penilaian_id.exists'           => 'Sub-penilaian tidak ditemukan.',

            // store_bobot (mapping)
            'rows.required'                     => 'Parameter rows (array) diperlukan untuk menyimpan bobot.',
            'rows.array'                        => 'Rows harus berupa array.',
            'rows.min'                          => 'Rows minimal harus berisi satu baris pemetaan.',
            'rows.*.cpmk_id.required'           => 'Setiap baris harus menyertakan cpmk_id.',
            'rows.*.cpmk_id.exists'             => 'CPMK pada baris tidak ditemukan.',
            'rows.*.bobot_acuan.numeric'        => 'bobot_acuan harus berupa angka.',
            'rows.*.bobot_acuan.min'            => 'bobot_acuan tidak boleh negatif.',
            'rows.*.sub-penilaian.required'       => 'Setiap baris harus memiliki sub-penilaian (daftar sub-penilaian dan bobot).',
            'rows.*.sub-penilaian.array'          => 'Sub-penilaian harus berupa array.',
            'rows.*.sub-penilaian.*.sub_penilaian_id.required' => 'Setiap sub-penilaian harus menyertakan sub_penilaian_id.',
            'rows.*.sub-penilaian.*.sub_penilaian_id.exists'   => 'Sub-penilaian pada sub-penilaian tidak ditemukan.',
            'rows.*.sub-penilaian.*.bobot.required'            => 'Setiap sub-penilaian harus menyertakan bobot.',
            'rows.*.sub-penilaian.*.bobot.numeric'             => 'Bobot sub-penilaian harus berupa angka.',
            'rows.*.sub-penilaian.*.bobot.min'                 => 'Bobot sub-penilaian tidak boleh negatif.',
        ];
    }
}
