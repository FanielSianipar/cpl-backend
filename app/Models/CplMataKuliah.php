<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CplMataKuliah extends Pivot
{
    protected $table = 'cpl_mata_kuliah';

    protected $primaryKey = 'cpl_mata_kuliah_id';
    public    $incrementing = true;
    public    $timestamps   = true;

    protected $fillable = [
        'mata_kuliah_id',
        'cpl_id',
        'bobot',
    ];
}
