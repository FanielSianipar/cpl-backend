<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubPenilaianCpmkMataKuliah extends Model
{
    protected $table = 'sub_penilaian_cpmk_mata_kuliah';
    public $incrementing = false;
    protected $primaryKey = ['sub_penilaian_id', 'cpmk_id', 'mata_kuliah_id', 'cpl_id'];

    protected $fillable = [
        'sub_penilaian_id',
        'mata_kuliah_id',
        'cpmk_id',
        'cpl_id',
        'bobot',
    ];

    public function subPenilaian()
    {
        return $this->belongsTo(SubPenilaian::class, 'sub_penilaian_id');
    }

    public function cpmkMataKuliah()
    {
        return $this->belongsTo(MataKuliahCpmkPivot::class, [
            'mata_kuliah_id',
            'cpmk_id',
            'cpl_id'
        ]);
    }

    public function cpmk()
    {
        return $this->belongsTo(CPMK::class, 'cpmk_id');
    }

    // (opsional) jika kamu butuh akses CPL-nya juga
    public function cpl()
    {
        return $this->belongsTo(CPL::class, 'cpl_id');
    }
}
