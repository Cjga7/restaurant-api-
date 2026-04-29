<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SucursalScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('sanctum')->user();

        // Solo filtra si el usuario tiene sucursal asignada (no es super_admin)
        if ($user && $user->sucursal_id !== null) {
            $builder->where($model->getTable() . '.sucursal_id', $user->sucursal_id);
        }
    }
}