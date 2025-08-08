<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CplMataKuliah extends Model
{
    protected $table = 'cpl_mata_kuliah';

    protected $primaryKey = 'cpl_mata_kuliah_id';

    protected $fillable = [
        'mata_kuliah_id',
        'cpl_id',
        'bobot',
    ];
}
