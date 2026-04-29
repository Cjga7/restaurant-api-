<?php

namespace App\Repositories;

use App\Models\MovimientoInventario;
use Illuminate\Database\Eloquent\Collection;

class MovimientoInventarioRepository
{
    public function all(array $filters = []): Collection
    {
        return MovimientoInventario::with(['item', 'user', 'sucursal'])
            ->when($filters['sucursal_id'] ?? null, fn($q, $v) => $q->where('sucursal_id', $v))
            ->when($filters['item_id'] ?? null,    fn($q, $v) => $q->where('item_id', $v))
            ->when($filters['tipo'] ?? null,       fn($q, $v) => $q->where('tipo', $v))
            ->when($filters['desde'] ?? null,      fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['hasta'] ?? null,      fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 100)
            ->get();
    }

    public function create(array $data): MovimientoInventario
    {
        return MovimientoInventario::create($data);
    }
}