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
        $action = $this->input('action');

        $rules = [
            'action' => 'required|in:store,update,view,delete',
        ];

        switch ($action) {
            case 'view':
                return array_merge($rules, [
                    'kelas_id' => 'sometimes|exists:kelas,kelas_id',
                ]);

            case 'store':
                return array_merge($rules, [
                    'kode_kelas'     => 'required|string|max:15|unique:kelas,kode_kelas',
                    'nama_kelas'     => 'required|string|max:50',
                    'semester'       => 'required|integer|max:2',
                    'tahun_ajaran'   => 'required|string|max:10',
                    'mata_kuliah_id' => 'required|exists:mata_kuliah,mata_kuliah_id',
                    'dosens'         => 'required|array|min:1',
                    'dosens.*.dosen_id'  => ['required', 'distinct', 'exists:users,id'],
                    'dosens.*.jabatan'   => [
                        'required',
                        Rule::in(['Dosen Utama', 'Pendamping Dosen 1', 'Pendamping Dosen 2']),
                    ],
                ]);

            case 'update':
                return array_merge($rules, [
                    'kelas_id'       => 'required|exists:kelas,kelas_id',
                    'kode_kelas'     => [
                        'required',
                        'string',
                        'max:15',
                        Rule::unique('kelas', 'kode_kelas')
                            ->ignore($this->input('kelas_id'), 'kelas_id')
                    ],
                    'nama_kelas'     => 'required|string|max:50',
                    'semester'       => 'required|integer|max:2',
                    'tahun_ajaran'   => 'required|string|max:10',
                    'mata_kuliah_id' => 'sometimes|exists:mata_kuliah,mata_kuliah_id',
                    'dosens'         => 'sometimes|array|min:1',
                    'dosens.*.dosen_id'  => ['required', 'distinct', 'exists:users,id'],
                    'dosens.*.jabatan'   => [
                        'required',
                        Rule::in(['Dosen Utama', 'Pendamping Dosen 1', 'Pendamping Dosen 2']),
                    ],
                ]);

            case 'delete':
                return array_merge($rules, [
                    'kelas_id' => 'required|exists:kelas,kelas_id',
                ]);

            default:
                return $rules;
        }
    }

    public function withValidator($validator)
    {
        // hanya jalankan bila action = store atau update
        $validator->after(function ($validator) {
            $action = $this->input('action');

            if (in_array($action, ['store', 'update'])) {
                $dosens = $this->input('dosens', []);

                // hitung berapa Dosen Utama
                $jumlahUtama = collect($dosens)
                    ->pluck('jabatan')
                    ->filter(fn($jabatan) => $jabatan === 'Dosen Utama')
                    ->count();

                if ($jumlahUtama < 1) {
                    $validator->errors()->add(
                        'dosens',
                        'Harus ada satu dosen yang memiliki jabatan Dosen Utama.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'action.required'           => 'Action wajib diisi.',
            'action.in'                 => 'Action tidak valid (store, update, view, delete).',

            'kelas_id.required'         => 'ID kelas wajib diisi.',
            'kelas_id.exists'           => 'Kelas tidak ditemukan.',

            'kode_kelas.required'       => 'Kode kelas wajib diisi.',
            'kode_kelas.max'            => 'Kode kelas maksimal 15 karakter.',
            'kode_kelas.unique'         => 'Kode kelas sudah digunakan.',

            'nama_kelas.required'       => 'Nama kelas wajib diisi.',
            'nama_kelas.max'            => 'Nama kelas maksimal 50 karakter.',

            'semester.required'         => 'Semester wajib diisi.',
            'semester.integer'          => 'Semester harus berupa angka.',
            'semester.max'              => 'Semester maksimal 2 digit.',

            'tahun_ajaran.required'     => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.max'          => 'Tahun ajaran maksimal 10 karakter.',

            'mata_kuliah_id.required'   => 'Mata kuliah wajib diisi.',
            'mata_kuliah_id.exists'     => 'Mata kuliah tidak ditemukan.',

            'dosens.required'           => 'Field dosens wajib diisi.',
            'dosens.array'              => 'Format dosen harus array.',
            'dosens.min'                => 'Minimal satu dosen harus dikirim.',

            'dosens.*.dosen_id.required' => 'Field dosen_id wajib diisi.',
            'dosens.*.dosen_id.distinct' => 'Dosen tidak boleh duplikat.',
            'dosens.*.dosen_id.exists'  => 'Dosen tidak ditemukan.',

            'dosens.*.jabatan.required' => 'Field jabatan wajib diisi.',
            'dosens.*.jabatan.in'       => 'Jabatan tidak valid.',
        ];
    }
}
