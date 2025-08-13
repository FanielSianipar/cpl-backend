<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DaftarMahasiswaKeKelasRequest extends FormRequest
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
        $action = $this->input('action');

        return match ($action) {
            'view' => [
                'action'       => ['required', 'in:view'],
                'mahasiswa_id' => ['sometimes', 'exists:mahasiswa,mahasiswa_id'],
                'kelas_id'     => ['sometimes', 'exists:kelas,kelas_id'],
            ],

            'store' => [
                'action'            => ['required', 'in:store'],
                'kelas_id'          => ['required', 'exists:kelas,kelas_id'],
                'mahasiswas'        => ['required', 'array', 'min:1'],
                'mahasiswas.*.npm'  => ['required', 'string', 'max:20', 'distinct'],
                'mahasiswas.*.name' => ['required', 'string', 'max:100'],
            ],

            'update' => [
                'action'         => ['required', 'in:update'],
                'mahasiswa_id'   => ['required', 'exists:mahasiswa,mahasiswa_id'],
                'npm'            => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('mahasiswa', 'npm')
                        ->ignore($this->input('mahasiswa_id'), 'mahasiswa_id')
                ],
                'name'           => ['sometimes', 'string', 'max:100'],
                'kelas_id'       => ['sometimes', 'exists:kelas,kelas_id'],
            ],

            'delete' => [
                'action'       => ['required', 'in:delete'],
                'mahasiswa_id' => ['required', 'exists:mahasiswa,mahasiswa_id'],
                'kelas_id'     => ['required', 'exists:kelas,kelas_id'],
            ],

            default => [
                'action' => ['required', 'in:view,store,update,delete']
            ],
        };
    }

    /**
     * Pesan error khusus untuk validasi field CPL.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Action
            'action.required'     => 'Action wajib diisi.',
            'action.in'           => 'Action harus salah satu: view, store, update, delete.',

            // mahasiswa_id (untuk view/update/delete)
            'mahasiswa_id.required' => 'Parameter mahasiswa_id wajib disertakan.',
            'mahasiswa_id.exists'   => 'Mahasiswa tersebut tidak ditemukan.',

            // kelas_id (untuk view/store/update/delete)
            'kelas_id.required'     => 'Parameter kelas_id wajib disertakan.',
            'kelas_id.exists'       => 'Kelas tersebut tidak ditemukan.',

            // Bulk store mahasiswa
            'mahasiswas.required'       => 'Daftar mahasiswa wajib diisi.',
            'mahasiswas.array'          => 'Format mahasiswas harus berupa array.',
            'mahasiswas.min'            => 'Minimal satu mahasiswa harus didaftarkan.',
            'mahasiswas.*.npm.required' => 'NPM setiap mahasiswa wajib diisi.',
            'mahasiswas.*.npm.distinct' => 'NPM dalam request tidak boleh duplikat.',
            'mahasiswas.*.npm.max'      => 'NPM maksimal 20 karakter.',
            'mahasiswas.*.name.required' => 'Nama setiap mahasiswa wajib diisi.',
            'mahasiswas.*.name.max'     => 'Nama maksimal 100 karakter.',
        ];
    }
}
