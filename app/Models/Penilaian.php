<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';
    protected $primaryKey = 'penilaian_id';
    protected $guarded = [];

    public function subPenilaian()
    {
        return $this->hasMany(SubPenilaian::class, 'penilaian_id','penilaian_id');
    }
}
