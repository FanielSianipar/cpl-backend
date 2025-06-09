<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MataKuliahCpmkPivot extends Pivot
{
    public $incrementing = false;
    protected $primaryKey = ['mata_kuliah_id', 'cpmk_id', 'cpl_id'];
}

