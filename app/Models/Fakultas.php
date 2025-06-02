<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use HasFactory;

    protected $primaryKey = 'fakultas_id';

    protected $table = 'fakultas';

    protected $fillable = [
        'kode_fakultas',
        'nama_fakultas',
    ];

    public function prodis()
    {
        return $this->hasMany(Prodi::class, 'fakultas_id', 'fakultas_id');
    }
}
