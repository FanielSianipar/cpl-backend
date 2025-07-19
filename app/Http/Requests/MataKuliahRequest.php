<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MataKuliahRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat/mengubah data Mata Kuliah.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Jika Anda ingin melakukan otorisasi, lakukan di sini
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan Mata Kuliah.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Ambil nilai action dari request
        $action = $this->input('action');

        // Jika action adalah view, kita tidak butuh validasi untuk field data Mata Kuliah
        if ($action === 'view') {
            return [
                // Opsional: jika parameter id disertakan, validasi keberadaan data mata kuliah
                'mata_kuliah_id' => 'sometimes|exists:mata_kuliah,mata_kuliah_id',
            ];
        }

        if ($action === 'update') {
            return [
                'mata_kuliah_id'    => 'required|exists:mata_kuliah,mata_kuliah_id',
                'kode_mata_kuliah'  => [
                    'required',
                    'string',
                    'max:15',
                    Rule::unique('mata_kuliah', 'kode_mata_kuliah')->ignore($this->input('mata_kuliah_id'), 'mata_kuliah_id')
                ],
                'nama_mata_kuliah'  => 'required|string|max:50',
                'prodi_id' => 'exists:prodi,prodi_id',
            ];
        }

        if ($action === 'delete') {
            return [
                'mata_kuliah_id'    => 'required|exists:mata_kuliah,mata_kuliah_id',
            ];
        }

        return [
            'kode_mata_kuliah'  => 'required|string|max:10|unique:mata_kuliah,kode_mata_kuliah',
            'nama_mata_kuliah'  => 'required|string|max:50',
            'prodi_id' => 'exists:prodi,prodi_id',
        ];
    }

    /**
     * Pesan error khusus untuk validasi field mata kuliah.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mata_kuliah_id.exists' => 'Mata kuliah dengan ID yang diberikan tidak ditemukan.',
            'mata_kuliah_id.required' => 'ID mata kuliah wajib dikirim.',
            'kode_mata_kuliah.required' => 'Kode mata kuliah wajib diisi.',
            'kode_mata_kuliah.string' => 'Kode mata kuliah harus berupa teks.',
            'kode_mata_kuliah.max' => 'Kode mata kuliah maksimal terdiri dari 15 karakter.',
            'kode_mata_kuliah.unique' => 'Kode mata kuliah sudah digunakan oleh mata kuliah lain.',
            'nama_mata_kuliah.required' => 'Nama mata kuliah wajib diisi.',
            'nama_mata_kuliah.string' => 'Nama mata kuliah harus berupa teks.',
            'nama_mata_kuliah.max' => 'Nama mata kuliah maksimal terdiri dari 50 karakter.',
            'prodi_id.exists' => 'Program studi tidak ditemukan.',
        ];
    }
}
