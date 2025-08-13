<?php

namespace App\Models;

use App\Scopes\ProdiScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ProdiScope);
    }

    protected $primaryKey = 'mahasiswa_id';

    protected $table = 'mahasiswa';

    protected $fillable = [
        'npm',
        'name',
        'angkatan',
        'prodi_id',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'prodi_id');
    }

    public function kelasDiikuti()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_mahasiswa', 'mahasiswa_id', 'kelas_id')
            ->withPivot('kelas_mahasiswa_id')
            ->withTimestamps();
    }
}
