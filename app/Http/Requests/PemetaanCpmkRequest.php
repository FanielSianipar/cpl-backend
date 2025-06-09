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
        $rules = [
            'action' => 'required|in:store,update,view,delete',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,mata_kuliah_id',
        ];

        $action = $this->input('action');

        if (in_array($action, ['store', 'update'])) {
            $rules['cpmks'] = 'required|array|min:1';
            $rules['cpmks.*.cpmk_id'] = 'required|exists:cpmk,cpmk_id';
            $rules['cpmks.*.cpl_id'] = 'required|exists:cpl,cpl_id';
            $rules['cpmks.*.bobot'] = 'required|numeric|min:0|max:100';
        } elseif ($action === 'delete') {
            // Untuk delete, kita tidak butuh field bobot
            $rules['cpmks'] = 'required|array|min:1';
            $rules['cpmks.*.cpmk_id'] = 'required|exists:cpmk,cpmk_id';
            $rules['cpmks.*.cpl_id'] = 'required|exists:cpl,cpl_id';
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
            'action.required' => 'Action harus diisi (store, update, view, atau delete).',
            'action.in' => 'Action yang diberikan tidak valid, pilih salah satu: store, update, view, delete.',
            'mata_kuliah_id.required' => 'ID mata kuliah wajib diisi.',
            'mata_kuliah_id.exists' => 'Mata kuliah tidak ditemukan.',

            'cpmks.required' => 'Data CPMK harus disertakan untuk aksi store atau update.',
            'cpmks.array' => 'Data CPMK harus berupa array.',
            'cpmks.min' => 'Minimal satu data CPMK harus disertakan.',

            'cpmks.*.cpmk_id.required' => 'ID CPMK wajib disertakan.',
            'cpmks.*.cpmk_id.exists' => 'CPMK yang dipilih tidak valid.',
            'cpmks.*.cpl_id.required' => 'ID CPL wajib disertakan untuk mapping CPMK.',
            'cpmks.*.cpl_id.exists' => 'CPL yang dipilih tidak valid.',
            'cpmks.*.bobot.required' => 'Bobot untuk CPMK wajib diisi.',
            'cpmks.*.bobot.numeric' => 'Bobot CPMK harus berupa angka.',
            'cpmks.*.bobot.min' => 'Bobot CPMK minimal 0.',
            'cpmks.*.bobot.max' => 'Bobot CPMK maksimal 100.',
        ];
    }
}
