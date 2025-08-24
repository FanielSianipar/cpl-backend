<?php

namespace App\Models;

use App\Scopes\ProdiScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CPL extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ProdiScope);
    }

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
    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(
            MataKuliah::class,
            'cpl_mata_kuliah',
            'cpl_id',
            'mata_kuliah_id'
        )
            ->withPivot('bobot')
            ->withTimestamps();
    }

    public function cpmks()
    {
        return $this->belongsToMany(CPMK::class, 'cpmk_mata_kuliah')
            ->withPivot('bobot')
            ->withTimestamps();
    }
}
