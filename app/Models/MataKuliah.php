<?php

namespace App\Models;

use App\Scopes\ProdiScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ProdiScope);
    }

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

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }

    /**
     * Relasi many-to-many ke CPL dengan pivot (dengan bobot).
     */
    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(
            CPL::class,
            'cpl_mata_kuliah',
            'cpl_id',
            'mata_kuliah_id',
        )
            ->using(CplMataKuliah::class)
            ->withPivot('cpl_mata_kuliah_id', 'bobot')
            ->withTimestamps();
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(
            CPMK::class,
            'mata_kuliah_id',
            'mata_kuliah_id'
        );
    }

    // jika cpmk dipakai berulang, maka memakai many-to-many
    /**
     * Relasi many-to-many ke CPMK dengan pivot.
     * Termasuk kolom cpl_id di pivot untuk mengetahui CPL mana yang menjadi induk.
     */
    // public function cpmks()
    // {
    //     return $this->belongsToMany(CPMK::class, 'cpmk_mata_kuliah', 'mata_kuliah_id', 'cpmk_id')
    //         ->withPivot('bobot', 'cpl_id')
    //         ->using(MataKuliahCpmkPivot::class)
    //         ->withTimestamps();
    // }
}
