<?php

namespace App\Models;

use App\Scopes\ProdiScope;
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
        'mata_kuliah_id',
    ];

    // jika cpmk hanya dipakai di satu mata kuliah
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }
    public function cpls()
    {
        return $this->belongsToMany(
            Cpl::class,
            'cpmk_cpl',
            'cpmk_id',
            'cpl_id'
        )->withPivot('bobot')
            ->withTimestamps();
    }

    // jika cpmk dipakai berulang, maka memakai many-to-many
    // public function prodi()
    // {
    //     return $this->belongsTo(Prodi::class, 'prodi_id', 'prodi_id');
    // }
    /**
     * Relasi many-to-many ke Mata Kuliah.
     */
    // public function mataKuliahs()
    // {
    //     return $this->belongsToMany(MataKuliah::class, 'cpmk_mata_kuliah', 'cpmk_id', 'mata_kuliah_id')
    //         ->withPivot('bobot', 'cpl_id')
    //         ->withTimestamps();
    // }

    public function subPenilaian()
    {
        return $this->belongsToMany(
            SubPenilaian::class,
            'sub_penilaian_cpmk_mk',
            'cpmk_id',
            'sub_penilaian_id'
        )
            ->withPivot(['mata_kuliah_id', 'cpl_id', 'bobot'])
            ->withTimestamps();
    }
}
