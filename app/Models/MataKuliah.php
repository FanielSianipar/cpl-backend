<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $primaryKey = 'mata_kuliah_id';

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'kode_mata_kuliah',
        'nama_mata_kuliah',
        'prodi_id',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'prodi_id');
    }

    /**
     * Relasi many-to-many ke CPL dengan pivot (dengan bobot).
     */
    public function cpls()
    {
        return $this->belongsToMany(CPL::class, 'cpl_mata_kuliah', 'mata_kuliah_id', 'cpl_id')
            ->withPivot('bobot')
            ->withTimestamps();
    }

    /**
     * Relasi many-to-many ke CPMK dengan pivot.
     * Termasuk kolom cpl_id di pivot untuk mengetahui CPL mana yang menjadi induk.
     */
    public function cpmks()
    {
        return $this->belongsToMany(CPMK::class, 'cpmk_mata_kuliah', 'mata_kuliah_id', 'cpmk_id')
            ->withPivot('bobot', 'cpl_id')
            ->using(MataKuliahCpmkPivot::class)
            ->withTimestamps();
    }
}
