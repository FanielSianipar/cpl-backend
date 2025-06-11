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
        // Jika field id tersedia (misalnya dikirim melalui JSON), pastikan sudah ter-merge.
        if ($this->has('id')) {
            $this->merge([
                'id' => $this->id, // Bisa juga cast ke integer jika diperlukan: (int) $this->id
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Validasi dasar yang harus ada untuk semua aksi
        $rules = [
            'action' => 'required|string|in:view,store,update,delete',
        ];

        // Jika aksi view, validasi jika ingin ambil record berdasarkan id
        if ($this->input('action') === 'view') {
            $rules = array_merge($rules, [
                'id' => ['sometimes', 'exists:users,id'],
            ]);
        }

        // Jika aksi store, validasi field yang diperlukan untuk pembuatan akun baru
        if ($this->input('action') === 'store') {
            $rules = array_merge($rules, [
                'name'     => ['required', 'string', 'min:1', 'max:255'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                'prodi_id' => ['required', 'integer', 'exists:prodi,prodi_id'],
            ]);
        }

        // Jika aksi update, validasi termasuk field id dan field lainnya
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
                'prodi_id' => ['required', 'integer', 'exists:prodi,prodi_id'],
            ]);
        }

        // Aturan untuk delete
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
            'prodi_id.required'   => 'Prodi wajib diisi.',
            'prodi_id.integer'    => 'Prodi harus berupa angka.',
            'prodi_id.exists'     => 'Prodi tidak ditemukan.',
        ];
    }
}
