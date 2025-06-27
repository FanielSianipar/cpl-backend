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
        'dosen_id',
        'semester',
        'tahun_ajaran'
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }
}
