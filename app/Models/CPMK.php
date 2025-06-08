<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CPMK extends Model
{
    use HasFactory;

    protected $primaryKey = 'cpmk_id';

    protected $table = 'cpmk';

    protected $fillable = [
        'kode_cpmk',
        'nama_cpmk',
        'deskripsi',
        'prodi_id',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'prodi_id');
    }

    /**
     * Relasi many-to-many ke Mata Kuliah.
     */
    public function mataKuliahs()
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_cpmk', 'cpmk_id', 'mata_kuliah_id')
            ->withPivot('percentage', 'cpl_id')
            ->withTimestamps();
    }
}
