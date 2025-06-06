<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CpmkRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat/mengubah data CPMK.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Jika Anda ingin melakukan otorisasi, lakukan di sini
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan CPMK.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Ambil nilai action dari request
        $action = $this->input('action');

        // Jika action adalah view, kita tidak butuh validasi untuk field data CPMK
        if ($action === 'view') {
            return [
                // Opsional: jika parameter id disertakan, validasi keberadaan data CPMK
                'cpmk_id' => 'sometimes|exists:cpmk,cpmk_id',
            ];
        }

        if ($action === 'update') {
            return [
                'cpmk_id'    => 'required|exists:cpmk,cpmk_id',
                'kode_cpmk'  => [
                    'required',
                    'string',
                    'max:15',
                    Rule::unique('cpmk', 'kode_cpmk')->ignore($this->input('cpmk_id'), 'cpmk_id')
                ],
                'nama_cpmk'  => 'required|string|max:50',
                'deskripsi' => 'required|string|max:500',
                'prodi_id' => 'sometimes|exists:prodi,prodi_id',
            ];
        }

        if ($action === 'delete') {
            return [
                'cpmk_id'    => 'required|exists:cpmk,cpmk_id',
            ];
        }

        return [
            'kode_cpmk'  => 'required|string|max:10|unique:cpmk,kode_cpmk',
            'nama_cpmk'  => 'required|string|max:50',
            'deskripsi' => 'required|string|max:500',
            'prodi_id' => 'required|exists:prodi,prodi_id',
        ];
    }

    /**
     * Pesan error khusus untuk validasi field CPMK.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cpmk_id.exists' => 'CPMK dengan ID yang diberikan tidak ditemukan.',
            'cpmk_id.required' => 'ID CPMK wajib dikirim.',
            'kode_cpmk.required' => 'Kode CPMK wajib diisi.',
            'kode_cpmk.string' => 'Kode CPMK harus berupa teks.',
            'kode_cpmk.max' => 'Kode CPMK maksimal terdiri dari 15 karakter.',
            'kode_cpmk.unique' => 'Kode CPMK sudah digunakan oleh CPMK lain.',
            'nama_cpmk.required' => 'Nama CPMK wajib diisi.',
            'nama_cpmk.string' => 'Nama CPMK harus berupa teks.',
            'nama_cpmk.max' => 'Nama CPMK maksimal terdiri dari 50 karakter.',
            'deskripsi.required' => 'Deskripsi CPMK wajib diisi.',
            'deskripsi.string' => 'Deskripsi CPMK harus berupa teks.',
            'deskripsi.max' => 'Deskripsi CPMK maksimal terdiri dari 500 karakter.',
            'prodi_id.required' => 'Program studi wajib diisi.',
            'prodi_id.exists' => 'Program studi tidak ditemukan.',
        ];
    }
}
