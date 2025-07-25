<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MataKuliahCpmkPivot extends Pivot
{
    protected $table = 'cpmk_mata_kuliah';
    public $incrementing = false;
    protected $primaryKey = ['mata_kuliah_id', 'cpmk_id', 'cpl_id'];

    public function subPenilaians()
    {
        return $this->hasMany(SubPenilaianCpmkMataKuliah::class, [
            'mata_kuliah_id',
            'cpmk_id',
            'cpl_id'
        ]);
    }
}
