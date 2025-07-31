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

        // Aturan dasar
        $rules = [
            'action'           => 'required|in:store,update,view,delete',
            'mata_kuliah_id'   => 'required|exists:mata_kuliah,mata_kuliah_id',
        ];

        // Tambahan untuk store & update
        if (in_array($action, ['store', 'update'])) {
            $rules['cpmks']              = 'required|array|min:1';
            $rules['cpmks.*.cpmk_id']    = 'required|exists:cpmk,cpmk_id';
            $rules['cpmks.*.cpl_id']     = 'required|exists:cpl,cpl_id';
            $rules['cpmks.*.bobot']      = 'required|numeric|min:0|max:100';
        }
        // Tambahan untuk view
        elseif ($action === 'view') {
            $rules['mata_kuliah_id'] = 'required';
        }
        // Tambahan untuk delete
        elseif ($action === 'delete') {
            $rules['cpmks']             = 'required|array|min:1';
            $rules['cpmks.*.cpmk_id']   = 'required|exists:cpmk,cpmk_id';
            $rules['cpmks.*.cpl_id']    = 'required|exists:cpl,cpl_id';
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
            'action.required'      => 'Action harus diisi (store, update, view, delete).',
            'action.in'            => 'Action tidak valid.',
            'mata_kuliah_id.required' => 'ID mata kuliah wajib diisi.',
            'mata_kuliah_id.exists'   => 'Mata kuliah tidak ditemukan.',

            // store/update
            'cpmks.required'           => 'Data CPMK harus disertakan.',
            'cpmks.array'              => 'Data CPMK harus berupa array.',
            'cpmks.min'                => 'Minimal satu data CPMK harus disertakan.',
            'cpmks.*.cpmk_id.required' => 'ID CPMK wajib diisi.',
            'cpmks.*.cpmk_id.exists'   => 'CPMK tidak ditemukan.',
            'cpmks.*.cpl_id.required'  => 'ID CPL wajib diisi.',
            'cpmks.*.cpl_id.exists'    => 'CPL tidak ditemukan.',
            'cpmks.*.bobot.required'   => 'Bobot CPMK wajib diisi.',
            'cpmks.*.bobot.numeric'    => 'Bobot CPMK harus berupa angka.',
            'cpmks.*.bobot.min'        => 'Bobot CPMK minimal 0.',
            'cpmks.*.bobot.max'        => 'Bobot CPMK maksimal 100.',

            // view & delete
            'cpmk_id.required'        => 'ID CPMK wajib disertakan.',
            'cpmk_id.exists'          => 'CPMK tidak ditemukan.',
            'cpl_id.required'         => 'ID CPL wajib disertakan untuk dihapus.',
            'cpl_id.exists'           => 'CPL tidak ditemukan.',
        ];
    }
}
