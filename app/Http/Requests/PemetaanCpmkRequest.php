<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PemetaanCpmkRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan permintaan ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Ubah logika otorisasi sesuai kebutuhan Anda
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan pemetaan CPMK.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $action = $this->input('action');

        // Aturan dasar untuk semua aksi
        $rules = [
            'action' => 'required|in:store,update,view,delete',
        ];

        // store & update memerlukan payload 'mata_kuliah_id' + array 'cpmks'
        if (in_array($action, ['store', 'update'])) {
            $rules['mata_kuliah_id']    = 'required|exists:mata_kuliah,mata_kuliah_id';
            $rules['cpmks']             = 'required|array|min:1';
            $rules['cpmks.*.cpmk_id']   = 'required|exists:cpmk,cpmk_id';
            $rules['cpmks.*.cpl_id']    = 'required|exists:cpl,cpl_id';
            $rules['cpmks.*.bobot']     = 'required|numeric|min:0|max:100';
        }
        // view hanya memerlukan 'mata_kuliah_id'
        elseif ($action === 'view') {
            $rules['mata_kuliah_id']    = 'required|exists:mata_kuliah,mata_kuliah_id';
        }
        // delete satu–satu berdasarkan pivot id
        elseif ($action === 'delete') {
            $rules['cpmk_mata_kuliah_id'] = 'required|exists:cpmk_mata_kuliah,cpmk_mata_kuliah_id';
        }

        return $rules;
    }

    /**
     * Menyesuaikan pesan error yang muncul untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required'                    => 'Action harus diisi (store, update, view, delete).',
            'action.in'                          => 'Action tidak valid, pilih salah satu: store, update, view, delete.',

            // store / update
            'mata_kuliah_id.required'           => 'ID mata kuliah wajib diisi.',
            'mata_kuliah_id.exists'             => 'Mata kuliah tidak ditemukan.',
            'cpmks.required'                    => 'Data CPMK harus disertakan untuk store/update.',
            'cpmks.array'                       => 'Data CPMK harus berupa array.',
            'cpmks.min'                         => 'Minimal satu item CPMK harus disertakan.',
            'cpmks.*.cpmk_id.required'          => 'ID CPMK wajib diisi.',
            'cpmks.*.cpmk_id.exists'            => 'CPMK tidak ditemukan.',
            'cpmks.*.cpl_id.required'           => 'ID CPL wajib diisi.',
            'cpmks.*.cpl_id.exists'             => 'CPL tidak ditemukan.',
            'cpmks.*.bobot.required'            => 'Bobot CPMK wajib diisi.',
            'cpmks.*.bobot.numeric'             => 'Bobot CPMK harus berupa angka.',
            'cpmks.*.bobot.min'                 => 'Bobot CPMK minimal 0.',
            'cpmks.*.bobot.max'                 => 'Bobot CPMK maksimal 100.',

            // view
            'mata_kuliah_id.required_if'        => 'ID mata kuliah wajib diisi untuk aksi view.',
            'mata_kuliah_id.exists_if'          => 'Mata kuliah untuk aksi view tidak ditemukan.',

            // delete per‐pivot
            'cpmk_mata_kuliah_id.required'      => 'ID pemetaan CPMK wajib disertakan.',
            'cpmk_mata_kuliah_id.exists'        => 'Pemetaan CPMK tidak ditemukan.',
        ];
    }
}
