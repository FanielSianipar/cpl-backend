<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAkunRequest extends FormRequest
{
    protected $stopOnFirstFailure = false;

    /**
     * Merge field id sehingga selalu tersedia di request.
     */
    protected function prepareForValidation()
    {
        if ($this->has('id')) {
            $this->merge([
                'id' => $this->id, // Bisa juga cast ke integer jika diperlukan: (int)$this->id
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Catatan:
     * - Untuk aksi *store* dan *update*, validasi prodi_id bersifat required kecuali request datang dari endpoint
     *   Admin Universitas (misalnya URI-nya: api/kelola-akun-admin-universitas) yang levelnya di atas prodi.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Aturan dasar untuk semua aksi
        $rules = [
            'action' => 'required|string|in:view,store,update,delete',
        ];

        // Tentukan apakah request berasal dari endpoint Admin Universitas
        // Misalnya, jika URI-nya mengandung "admin-universitas", maka kita anggap user yang dibuat akan menjadi Admin Universitas
        $isAdminUniversitas = $this->is('api/kelola-akun-admin-universitas');

        // Aksi view
        if ($this->input('action') === 'view') {
            $rules = array_merge($rules, [
                'id' => ['sometimes', 'exists:users,id'],
            ]);
        }

        // Aksi store
        if ($this->input('action') === 'store') {
            $rules = array_merge($rules, [
                'name'     => ['required', 'string', 'min:1', 'max:255'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                // Jika bukan Admin Universitas, prodi_id wajib diisi; untuk Admin Universitas, nullable.
                'prodi_id' => [
                    $isAdminUniversitas ? 'nullable' : 'required',
                    'integer',
                    'exists:prodi,prodi_id',
                ],
            ]);
        }

        // Aksi update
        if ($this->input('action') === 'update') {
            $rules = array_merge($rules, [
                'id'       => ['required', 'exists:users,id'],
                'name'     => ['required', 'string', 'max:255'],
                'email'    => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($this->id),
                ],
                'password' => ['nullable', 'string', 'min:8'],
                'prodi_id' => [
                    $isAdminUniversitas ? 'nullable' : 'required',
                    'sometimes',
                    'integer',
                    'exists:prodi,prodi_id',
                ],
            ]);
        }

        // Aksi delete
        if ($this->input('action') === 'delete') {
            $rules = array_merge($rules, [
                'id' => ['required', 'exists:users,id'],
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'id.required'         => 'ID akun wajib dikirim untuk update.',
            'id.exists'           => 'Akun dengan ID tersebut tidak ditemukan.',
            'name.required'       => 'Nama wajib diisi.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email sudah digunakan, silakan pilih email lain.',
            'password.required'   => 'Password wajib diisi.',
            'password.min'        => 'Password minimal harus 8 karakter.',
            'password.confirmed'  => 'Konfirmasi password tidak sesuai.',
            'prodi_id.required'   => 'Prodi wajib diisi jika bukan Admin Universitas.',
            'prodi_id.integer'    => 'Prodi harus berupa angka.',
            'prodi_id.exists'     => 'Prodi tidak ditemukan.',
        ];
    }
}
