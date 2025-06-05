<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;

    protected $primaryKey = 'prodi_id';

    protected $table = 'prodi';

    protected $fillable = [
        'kode_prodi',
        'nama_prodi',
        'fakultas_id',
    ];

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id', 'fakultas_id');
    }

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'prodi_id', 'prodi_id');
    }

    public function mata_kuliahs()
    {
        return $this->hasMany(MataKuliah::class, 'prodi_id', 'prodi_id');
    }
    public function cpls()
    {
        return $this->hasMany(CPL::class, 'prodi_id', 'prodi_id');
    }
}
