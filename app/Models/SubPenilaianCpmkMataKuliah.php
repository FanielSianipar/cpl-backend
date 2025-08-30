<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SubPenilaianCpmkMataKuliah extends Pivot
{
    protected $table = 'sub_penilaian_cpmk_mata_kuliah';
    protected $primaryKey = 'sub_penilaian_cpmk_mata_kuliah_id';

    public $incrementing = true;
    public $timestamps   = true;

    protected $fillable = [
        'sub_penilaian_id',
        'cpmk_id',
        'bobot',
    ];

    public function subPenilaian()
    {
        return $this->belongsTo(SubPenilaian::class, 'sub_penilaian_id');
    }

    // public function cpmkMataKuliah()
    // {
    //     return $this->belongsTo(MataKuliahCpmkPivot::class, [
    //         'mata_kuliah_id',
    //         'cpmk_id',
    //         'cpl_id'
    //     ]);
    // }

    public function cpmk()
    {
        return $this->belongsTo(CPMK::class, 'cpmk_id');
    }

    // (opsional) jika kamu butuh akses CPL-nya juga
    // public function cpl()
    // {
    //     return $this->belongsTo(CPL::class, 'cpl_id');
    // }
}
