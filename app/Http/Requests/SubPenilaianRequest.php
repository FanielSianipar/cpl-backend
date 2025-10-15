<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubPenilaianRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        // Anda bisa tambahkan cek permission/role di sini
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if (! empty($input['cpmks']) && is_array($input['cpmks'])) {
            foreach ($input['cpmks'] as $i => $item) {
                if (! array_key_exists('bobot', $item) || $item['bobot'] === null || $item['bobot'] === '') {
                    $input['cpmks'][$i]['bobot'] = 0;
                }
            }
        }

        $this->replace($input);
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request sub-penilaian.
     */
    public function rules(): array
    {
        $rules = [
            'action' => 'required|in:store,update,view,delete',
        ];

        $action = $this->input('action');

        if ($action === 'view') {
            // view: minimal salah satu sub_penilaian_id atau kelas_id
            $rules += [
                'sub_penilaian_id' => [
                    'sometimes',
                    'required_without:kelas_id',
                    'exists:sub_penilaian,sub_penilaian_id'
                ],
                'kelas_id'         => [
                    'sometimes',
                    'required_without:sub_penilaian_id',
                    'exists:kelas,kelas_id'
                ],
            ];
        } elseif ($action === 'store') {
            $rules += [
                'penilaian_id'             => ['required', 'exists:penilaian,penilaian_id'],
                'kelas_id'                 => ['required', 'exists:kelas,kelas_id'],
                'nama_sub_penilaian'       => ['required', 'string', 'max:100'],
                'cpmks'                    => ['required', 'array', 'min:1'],
                'cpmks.*.cpmk_id'          => ['required', 'exists:cpmk,cpmk_id'],
                'cpmks.*.bobot'            => ['sometimes', 'nullable', 'numeric', 'min:0'],
            ];
        } elseif ($action === 'update') {
            $rules += [
                'sub_penilaian_id'         => ['required', 'exists:sub_penilaian,sub_penilaian_id'],
                'penilaian_id'             => ['sometimes', 'exists:penilaian,penilaian_id'],
                'kelas_id'                 => ['sometimes', 'exists:kelas,kelas_id'],
                'nama_sub_penilaian'       => ['sometimes', 'string', 'max:100'],
                'cpmks'                    => ['sometimes', 'array', 'min:1'],
                'cpmks.*.cpmk_id'          => ['required_with:cpmks', 'exists:cpmk,cpmk_id'],
                'cpmks.*.bobot'            => ['required_with:cpmks', 'numeric', 'min:0'],
            ];
        } elseif ($action === 'delete') {
            $rules['sub_penilaian_id'] = ['required', 'exists:sub_penilaian,sub_penilaian_id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'action.required'                   => 'Action harus diisi (store, update, view, atau delete).',
            'action.in'                         => 'Action tidak valid, pilih: store, update, view, delete.',

            // view
            'sub_penilaian_id.required_without' => 'ID sub-penilaian wajib diisi jika kelas_id kosong.',
            'sub_penilaian_id.exists'           => 'Sub-penilaian tidak ditemukan.',
            'kelas_id.required_without'         => 'ID kelas wajib diisi jika sub_penilaian_id kosong.',
            'kelas_id.exists'                   => 'Kelas tidak ditemukan.',

            // store
            'penilaian_id.required'             => 'ID penilaian wajib diisi.',
            'penilaian_id.exists'               => 'Penilaian tidak ditemukan.',
            'kelas_id.required'                 => 'ID kelas wajib diisi.',
            'nama_sub_penilaian.required'       => 'Nama sub-penilaian wajib diisi.',
            'nama_sub_penilaian.string'         => 'Nama sub-penilaian harus berupa teks.',
            'nama_sub_penilaian.max'            => 'Nama sub-penilaian maksimal 100 karakter.',
            'cpmks.required'                    => 'Data CPMK-CPL wajib disertakan untuk aksi store/update.',
            'cpmks.array'                       => 'Data CPMK-CPL harus berupa array.',
            'cpmks.min'                         => 'Minimal satu data CPMK-CPL harus disertakan.',
            'cpmks.*.cpmk_id.required'          => 'ID CPMK wajib diisi.',
            'cpmks.*.cpmk_id.exists'            => 'CPMK tidak valid.',
            // 'cpmks.*.bobot.required'            => 'Bobot CPMK wajib diisi.',
            'cpmks.*.bobot.numeric'             => 'Bobot CPMK harus berupa angka.',
            'cpmks.*.bobot.min'                 => 'Bobot CPMK minimal 0.',

            // delete
            'sub_penilaian_id.required'         => 'ID sub-penilaian diperlukan untuk aksi delete.',
        ];
    }
}
