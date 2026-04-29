<?php

namespace App\Repositories;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Collection;

class PagoRepository
{
    public function all(array $filters = []): Collection
    {
        return Pago::with(['pedido', 'cajero', 'sesion'])
            ->when($filters['caja_sesion_id'] ?? null, fn($q, $v) => $q->where('caja_sesion_id', $v))
            ->when($filters['metodo'] ?? null,         fn($q, $v) => $q->where('metodo', $v))
            ->when($filters['desde'] ?? null,          fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['hasta'] ?? null,          fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 100)
            ->get();
    }

    public function create(array $data): Pago
    {
        return Pago::create($data);
    }

    public function findByPedido(int $pedidoId): ?Pago
    {
        return Pago::where('pedido_id', $pedidoId)->first();
    }
}