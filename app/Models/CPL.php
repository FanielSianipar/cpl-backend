<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CPL extends Model
{
    use HasFactory;

    protected $primaryKey = 'cpl_id';

    protected $table = 'cpl';

    protected $fillable = [
        'kode_cpl',
        'nama_cpl',
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
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_cpl', 'cpl_id', 'mata_kuliah_id')
            ->withPivot('bobot')
            ->withTimestamps();
    }
}
