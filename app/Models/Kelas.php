<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $primaryKey = 'kelas_id';

    protected $table = 'kelas';

    protected $fillable = [
        'mata_kuliah_id',
        'kode_kelas',
        'nama_kelas',
        'semester',
        'tahun_ajaran'
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }

    // pivot ke dosen (users)
    public function dosens()
    {
        return $this->belongsToMany(User::class, 'kelas_dosen', 'kelas_id', 'dosen_id')
            ->withPivot('jabatan')
            ->withTimestamps();
    }

    // helper: dosen utama
    public function dosenUtama()
    {
        return $this->dosens()->wherePivot('jabatan', 'Dosen Utama');
    }

    public function pendamping1()
    {
        return $this->dosens()->wherePivot('jabatan', 'Pendamping Dosen 1');
    }

    public function pendamping2()
    {
        return $this->dosens()->wherePivot('jabatan', 'Pendamping Dosen 2');
    }


    public function subPenilaian()
    {
        return $this->hasMany(SubPenilaian::class, 'kelas_id', 'kelas_id');
    }
}
