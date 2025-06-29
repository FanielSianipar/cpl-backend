<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat/mengubah data Kelas.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Jika Anda ingin melakukan otorisasi, lakukan di sini
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan Kelas.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Ambil nilai action dari request
        $action = $this->input('action');

        // Jika action adalah view, kita tidak butuh validasi untuk field data Kelas
        if ($action === 'view') {
            return [
                // Opsional: jika parameter id disertakan, validasi keberadaan data kelas
                'kelas_id' => 'sometimes|exists:kelas,kelas_id',
            ];
        }

        if ($action === 'update') {
            return [
                'kelas_id'    => 'required|exists:kelas,kelas_id',
                'kode_kelas'  => [
                    'required',
                    'string',
                    'max:15',
                    Rule::unique('kelas', 'kode_kelas')->ignore($this->input('kelas_id'), 'kelas_id')
                ],
                'nama_kelas'  => 'required|string|max:50',
                'semester'    => 'required|integer|max:2',
                'tahun_ajaran' => 'required|string|max:10',
                'mata_kuliah_id' => 'sometimes|exists:mata_kuliah,mata_kuliah_id',
                'dosens'            => 'sometimes|array',
                'dosens.*.dosen_id' => [
                    'required',
                    'exists:users,id'
                ],
                'dosens.*.jabatan'  => [
                    'required',
                    Rule::in(['Dosen Utama', 'Pendamping Dosen 1', 'Pendamping Dosen 2'])
                ],
            ];
        }

        if ($action === 'delete') {
            return [
                'kelas_id'    => 'required|exists:kelas,kelas_id',
            ];
        }

        return [
            'kode_kelas'  => 'required|string|max:15|unique:kelas,kode_kelas',
            'nama_kelas'  => 'required|string|max:50',
            'semester'    => 'required|integer|max:2',
            'tahun_ajaran' => 'required|string|max:10',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,mata_kuliah_id',
            'dosens'            => 'sometimes|array',
            'dosens.*.dosen_id' => [
                'required',
                'exists:users,id'
            ],
            'dosens.*.jabatan'  => [
                'required',
                Rule::in(['Dosen Utama', 'Pendamping Dosen 1', 'Pendamping Dosen 2'])
            ],

        ];
    }

    /**
     * Pesan error khusus untuk validasi field kelas.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kelas_id.exists' => 'Kelas dengan ID yang diberikan tidak ditemukan.',
            'kelas_id.required' => 'ID kelas wajib dikirim.',
            'kode_kelas.required' => 'Kode kelas wajib diisi.',
            'kode_kelas.string' => 'Kode kelas harus berupa teks.',
            'kode_kelas.max' => 'Kode kelas maksimal terdiri dari 15 karakter.',
            'kode_kelas.unique' => 'Kode kelas sudah digunakan oleh kelas lain.',
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.string' => 'Nama kelas harus berupa teks.',
            'nama_kelas.max' => 'Nama kelas maksimal terdiri dari 50 karakter.',
            'semester.required' => 'Semester wajib diisi.',
            'semester.integer' => 'Semester harus berupa angka.',
            'semester.max' => 'Semester maksimal 2 angka.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.string' => 'Tahun ajaran harus berupa teks.',
            'tahun_ajaran.max' => 'Tahun ajaran maksimal terdiri dari 10 karakter.',
            'mata_kuliah_id.required' => 'Mata kuliah wajib diisi.',
            'mata_kuliah_id.exists' => 'Mata kuliah tidak ditemukan.',
        ];
    }
}
