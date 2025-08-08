<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PemetaanCplRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        // Ubah logika otorisasi sesuai kebutuhan Anda
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $action = $this->input('action');

        $rules = [
            'action' => 'required|in:store,update,view,delete',
        ];

        if (in_array($action, ['store', 'update'])) {
            $rules['mata_kuliah_id']   = 'required|exists:mata_kuliah,mata_kuliah_id';
            $rules['cpls']             = 'required|array|min:1';
            $rules['cpls.*.cpl_id']    = 'required|exists:cpl,cpl_id|distinct';
            $rules['cpls.*.bobot']     = 'required|numeric|min:0|max:100';
        } elseif ($action === 'view') {
            $rules['mata_kuliah_id']   = 'required|exists:mata_kuliah,mata_kuliah_id';
        } elseif ($action === 'delete') {
            $rules['cpl_mata_kuliah_id'] = 'required|exists:cpl_mata_kuliah,cpl_mata_kuliah_id';
        }

        return $rules;
    }

    /**
     * Menyesuaikan pesan error yang muncul.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Action harus diisi (store, update, view, atau delete).',
            'action.in' => 'Action yang diberikan tidak valid, pilih salah satu: store, update, view, delete.',
            'mata_kuliah_id.required' => 'ID mata kuliah wajib diisi.',
            'mata_kuliah_id.exists' => 'Mata kuliah tidak ditemukan.',
            'cpls.required' => 'Data CPL harus disertakan untuk aksi store atau update.',
            'cpls.array' => 'Data CPL harus berupa array.',
            'cpls.min' => 'Minimal satu data CPL harus disertakan.',
            'cpls.*.cpl_id.required' => 'ID CPL wajib disertakan.',
            'cpls.*.cpl_id.exists' => 'CPL yang dipilih tidak valid.',
            'cpls.*.cpl_id.distinct' => 'Tidak boleh ada CPL yang sama dalam satu mata kuliah.',
            'cpls.*.bobot.required' => 'Bobot untuk CPL wajib diisi.',
            'cpls.*.bobot.numeric' => 'Bobot CPL harus berupa angka.',
            'cpls.*.bobot.min' => 'Bobot CPL minimal 0.',
            'cpls.*.bobot.max' => 'Bobot CPL maksimal 100.',
            'cpl_mata_kuliah_id.required' => 'ID pemetaan CPL wajib disertakan.',
            'cpl_mata_kuliah_id.exists'   => 'Pemetaan CPL tidak ditemukan.',
        ];
    }
}
