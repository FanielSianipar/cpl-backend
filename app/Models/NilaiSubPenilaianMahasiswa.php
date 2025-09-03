<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSubPenilaianMahasiswa extends Model
{
    protected $primaryKey = 'nilai_sub_penilaian_mahasiswa_id';

    protected $table    = 'nilai_sub_penilaian_mahasiswa';

    protected $fillable = [
        'sub_penilaian_cpmk_mata_kuliah_id',
        'mahasiswa_id',
        'nilai_mentah',
        'nilai_terbobot',
    ];

    public function subPenilaianCpmkPivot(): BelongsTo
    {
        return $this->belongsTo(
            SubPenilaianCpmkMataKuliah::class,
            'sub_penilaian_cpmk_mata_kuliah_id'
        );
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
