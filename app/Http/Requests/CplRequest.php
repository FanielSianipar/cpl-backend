<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CplRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat/mengubah data CPL.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Jika Anda ingin melakukan otorisasi, lakukan di sini
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan CPL.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Ambil nilai action dari request
        $action = $this->input('action');

        // Jika action adalah view, kita tidak butuh validasi untuk field data CPL
        if ($action === 'view') {
            return [
                // Opsional: jika parameter id disertakan, validasi keberadaan data CPL
                'cpl_id' => 'sometimes|exists:cpl,cpl_id',
            ];
        }

        if ($action === 'update') {
            return [
                'cpl_id'    => 'required|exists:cpl,cpl_id',
                'kode_cpl'  => [
                    'required',
                    'string',
                    'max:15',
                    Rule::unique('cpl', 'kode_cpl')->ignore($this->input('cpl_id'), 'cpl_id')
                ],
                'nama_cpl'  => 'required|string|max:50',
                'deskripsi' => 'required|string|max:500',
                'prodi_id' => 'exists:prodi,prodi_id',
            ];
        }

        if ($action === 'delete') {
            return [
                'cpl_id'    => 'required|exists:cpl,cpl_id',
            ];
        }

        return [
            'kode_cpl'  => 'required|string|max:10|unique:cpl,kode_cpl',
            'nama_cpl'  => 'required|string|max:50',
            'deskripsi' => 'required|string|max:500',
            'prodi_id' => 'exists:prodi,prodi_id',
        ];
    }

    /**
     * Pesan error khusus untuk validasi field CPL.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cpl_id.exists' => 'CPL dengan ID yang diberikan tidak ditemukan.',
            'cpl_id.required' => 'ID CPL wajib dikirim.',
            'kode_cpl.required' => 'Kode CPL wajib diisi.',
            'kode_cpl.string' => 'Kode CPL harus berupa teks.',
            'kode_cpl.max' => 'Kode CPL maksimal terdiri dari 15 karakter.',
            'kode_cpl.unique' => 'Kode CPL sudah digunakan oleh CPL lain.',
            'nama_cpl.required' => 'Nama CPL wajib diisi.',
            'nama_cpl.string' => 'Nama CPL harus berupa teks.',
            'nama_cpl.max' => 'Nama CPL maksimal terdiri dari 50 karakter.',
            'deskripsi.required' => 'Deskripsi CPL wajib diisi.',
            'deskripsi.string' => 'Deskripsi CPL harus berupa teks.',
            'deskripsi.max' => 'Deskripsi CPL maksimal terdiri dari 500 karakter.',
            'prodi_id.exists' => 'Program studi tidak ditemukan.',
        ];
    }
}
