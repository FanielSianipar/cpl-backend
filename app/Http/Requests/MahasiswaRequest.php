<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MahasiswaRequest extends FormRequest
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
     * Dapatkan aturan validasi yang berlaku untuk permintaan Mahasiswa.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Ambil nilai action dari request
        $action = $this->input('action');

        // Jika action adalah view, kita tidak butuh validasi untuk field data Mahasiswa
        if ($action === 'view') {
            return [
                // Opsional: jika parameter id disertakan, validasi keberadaan data mahasiswa
                'mahasiswa_id' => 'sometimes|exists:mahasiswa,mahasiswa_id',
            ];
        }

        if ($action === 'update') {
            return [
                'mahasiswa_id'    => 'required|exists:mahasiswa,mahasiswa_id',
                'npm'  => [
                    'required',
                    'string',
                    'max:10',
                    Rule::unique('mahasiswa', 'npm')->ignore($this->input('mahasiswa_id'), 'mahasiswa_id')
                ],
                'name'  => 'required|string|max:50',
                'email'    => [
                    'required',
                    'email',
                    Rule::unique('mahasiswa', 'email')->ignore($this->input('mahasiswa_id'), 'mahasiswa_id')
                ],
            ];
        }

        if ($action === 'delete') {
            return [
                'mahasiswa_id'    => 'required|exists:mahasiswa,mahasiswa_id',
            ];
        }

        return [
            'npm'  => 'required|string|max:10|unique:mahasiswa,npm',
            'name'  => 'required|string|max:50',
            'email'    => ['required', 'email', 'unique:mahasiswa,email'],
        ];
    }

    /**
     * Pesan error khusus untuk validasi field mahasiswa.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mahasiswa_id.exists' => 'Mahasiswa dengan ID yang diberikan tidak ditemukan.',
            'mahasiswa_id.required' => 'ID mahasiswa wajib dikirim.',
            'npm.required' => 'NPM wajib diisi.',
            'npm.string' => 'NPM harus berupa teks.',
            'npm.max' => 'NPM maksimal terdiri dari 10 karakter.',
            'npm.unique' => 'NPM sudah digunakan oleh mahasiswa lain.',
            'name.required' => 'Nama mahasiswa wajib diisi.',
            'name.string' => 'Nama mahasiswa harus berupa teks.',
            'name.max' => 'Nama mahasiswa maksimal terdiri dari 50 karakter.',
            'email.required' => 'Email mahasiswa wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh mahasiswa lain.',
        ];
    }
}
