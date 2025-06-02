<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdiRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat/mengubah data Prodi.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Jika Anda ingin melakukan otorisasi, lakukan di sini
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan Prodi.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Ambil nilai action dari request
        $action = $this->input('action');

        // Jika action adalah view, kita tidak butuh validasi untuk field data prodi
        if ($action === 'view') {
            return [
                // Opsional: jika parameter id disertakan, validasi keberadaan data prodi
                'prodi_id' => 'sometimes|exists:prodi,prodi_id',
            ];
        }

        if ($action === 'update') {
            return [
                'prodi_id'    => 'required|exists:prodi,prodi_id',
                'kode_prodi'  => [
                    'required',
                    'string',
                    'max:10',
                    Rule::unique('prodi', 'kode_prodi')->ignore($this->input('prodi_id'), 'prodi_id')
                ],
                'nama_prodi'  => 'required|string|max:50',
                'fakultas_id' => 'required|exists:fakultas,fakultas_id',
            ];
        }

        if ($action === 'delete') {
            return [
                'prodi_id'    => 'required|exists:prodi,prodi_id',
            ];
        }

        return [
            'kode_prodi'  => 'required|string|max:10|unique:prodi,kode_prodi',
            'nama_prodi'  => 'required|string|max:50',
            'fakultas_id' => 'required|exists:fakultas,fakultas_id',
        ];
    }

    /**
     * Pesan error khusus untuk validasi field Prodi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prodi_id.required' => 'ID akun wajib dikirim untuk update.',
            'prodi_id.exists'   => 'Akun dengan ID tersebut tidak ditemukan.',
            'kode_prodi.required'  => 'Kode Prodi wajib diisi.',
            'kode_prodi.string'    => 'Kode Prodi harus berupa teks.',
            'kode_prodi.max'       => 'Kode Prodi maksimal 10 karakter.',
            'kode_prodi.unique'    => 'Kode Prodi sudah digunakan.',
            'nama_prodi.required'  => 'Nama Prodi wajib diisi.',
            'nama_prodi.string'    => 'Nama Prodi harus berupa teks.',
            'nama_prodi.max'       => 'Nama Prodi maksimal 50 karakter.',
            'fakultas_id.required' => 'Fakultas ID wajib diisi.',
            'fakultas_id.exists'   => 'Fakultas tidak ditemukan.',
        ];
    }
}
