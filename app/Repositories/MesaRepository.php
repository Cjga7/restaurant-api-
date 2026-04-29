<?php

namespace App\Repositories;

use App\Models\Mesa;
use Illuminate\Database\Eloquent\Collection;

class MesaRepository
{
    public function all(?string $estado = null, ?int $sucursalId = null): Collection
    {
        return Mesa::with('sucursal')
                   ->when($estado, fn($q) => $q->where('estado', $estado))
                   ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                   ->orderBy('numero')
                   ->get();
    }

    public function findOrFail(int $id): Mesa
    {
        return Mesa::with('sucursal')->findOrFail($id);
    }

    public function create(array $data): Mesa
    {
        return Mesa::create($data);
    }

    public function update(Mesa $mesa, array $data): Mesa
    {
        $mesa->update($data);
        return $mesa->fresh('sucursal');
    }

    public function delete(Mesa $mesa): void
    {
        $mesa->delete();
    }

    public function cambiarEstado(Mesa $mesa, string $estado): Mesa
    {
        $mesa->update(['estado' => $estado]);
        return $mesa->fresh();
    }
}