<?php

namespace App\Repositories;

use App\Models\CajaSesion;
use Illuminate\Database\Eloquent\Collection;

class CajaSesionRepository
{
    public function all(array $filters = []): Collection
    {
        return CajaSesion::with(['cajero', 'sucursal', 'pagos'])
            ->when($filters['estado'] ?? null,      fn($q, $v) => $q->where('estado', $v))
            ->when($filters['sucursal_id'] ?? null, fn($q, $v) => $q->where('sucursal_id', $v))
            ->when($filters['cajero_id'] ?? null,   fn($q, $v) => $q->where('cajero_id', $v))
            ->orderBy('fecha_apertura', 'desc')
            ->get();
    }

    public function findOrFail(int $id): CajaSesion
    {
        return CajaSesion::with(['cajero', 'sucursal', 'pagos.pedido'])->findOrFail($id);
    }

    public function sesionAbierta(int $cajeroId, int $sucursalId): ?CajaSesion
{
    return CajaSesion::withoutGlobalScopes()
                     ->where('cajero_id', $cajeroId)
                     ->where('sucursal_id', $sucursalId)
                     ->where('estado', 'abierta')
                     ->first();
}
    public function create(array $data): CajaSesion
    {
        return CajaSesion::create($data);
    }

    public function update(CajaSesion $sesion, array $data): CajaSesion
    {
        $sesion->update($data);
        return $sesion->fresh(['cajero', 'sucursal', 'pagos']);
    }
    public function sesionesActivas(?int $sucursalId = null): \Illuminate\Database\Eloquent\Collection
{
    return CajaSesion::withoutGlobalScopes()
        ->with(['cajero', 'sucursal', 'pagos'])
        ->where('estado', 'abierta')
        ->when($sucursalId, fn($q, $v) => $q->where('sucursal_id', $v))
        ->orderBy('fecha_apertura', 'desc')
        ->get();
}
}