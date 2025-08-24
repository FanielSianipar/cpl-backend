<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CPMK extends Model
{
    use HasFactory;

    protected $table = 'cpmk';

    protected $primaryKey = 'cpmk_id';

    protected $fillable = [
        'kode_cpmk',
        'nama_cpmk',
        'deskripsi',
        'mata_kuliah_id',
    ];

    // jika cpmk hanya dipakai di satu mata kuliah
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(
            MataKuliah::class,
            'mata_kuliah_id',
            'mata_kuliah_id'
        );
    }

    public function cpls()
    {
        return $this->belongsToMany(CPL::class, 'cpmk_mata_kuliah', 'cpmk_id', 'cpl_id')
            ->using(MataKuliahCpmkPivot::class)
            ->withPivot('cpmk_mata_kuliah_id', 'bobot')
            ->withTimestamps();
    }

    public function subPenilaians(): BelongsToMany
    {
        return $this->belongsToMany(
            SubPenilaian::class,
            'sub_penilaian_cpmk_mata_kuliah',
            'sub_penilaian_id',
            'cpmk_id'
        )
            ->using(SubPenilaianCpmkMataKuliah::class)
            ->withPivot(['sub_penilaian_cpmk_mata_kuliah_id', 'bobot'])
            ->withTimestamps();
    }
}
