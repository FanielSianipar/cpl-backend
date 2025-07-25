<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubPenilaian extends Model
{
    use HasFactory;

    protected $table = 'sub_penilaian';
    protected $primaryKey = 'sub_penilaian_id';
    protected $fillable = [
        'penilaian_id',
        'kelas_id',
        'nama_sub_penilaian',
    ];

    public function penilaian()
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id', 'penilaian_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function cpmkPivotData()
    {
        return $this->hasMany(SubPenilaianCpmkMataKuliah::class, 'sub_penilaian_id');
    }

    public function cpmks()
    {
        return $this->belongsToMany(CPMK::class, 'sub_penilaian_cpmk_mata_kuliah', 'sub_penilaian_id', 'cpmk_id')
            ->withPivot(['mata_kuliah_id', 'cpl_id', 'bobot'])
            ->withTimestamps();
    }
}
