<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProdiScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Terapkan filter hanya jika user terautentikasi dan bukan Admin Universitas
        if (auth()->check() && !auth()->user()->hasRole('Admin Universitas')) {
            $builder->where('prodi_id', auth()->user()->prodi_id);
        }
    }
}
