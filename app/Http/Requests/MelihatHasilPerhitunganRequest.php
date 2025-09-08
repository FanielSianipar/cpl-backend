<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MelihatHasilPerhitunganRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk melihat hasil perhitungan.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Jika Anda ingin melakukan otorisasi, lakukan di sini
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan melihat hasil perhitungan.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kelas_id' => 'required|integer|exists:kelas,kelas_id',
        ];
    }

    public function messages(): array
    {
        return [
            'kelas_id.required' => 'Parameter kelas_id wajib disertakan.',
            'kelas_id.integer'  => 'kelas_id harus berupa angka.',
            'kelas_id.exists'   => 'Kelas tidak ditemukan.',
        ];
    }
}
