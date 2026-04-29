<?php

namespace App\Repositories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Collection;

class EmpleadoRepository
{
    public function all(?string $cargo = null, ?int $sucursalId = null): Collection
    {
        return Empleado::with(['sucursal', 'user'])
                       ->when($cargo, fn($q) => $q->where('cargo', $cargo))
                       ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                       ->orderBy('apellidos')
                       ->orderBy('nombres')
                       ->get();
    }

    public function findOrFail(int $id): Empleado
    {
        return Empleado::with(['sucursal', 'user'])->findOrFail($id);
    }

    public function create(array $data): Empleado
    {
        return Empleado::create($data);
    }

    public function update(Empleado $empleado, array $data): Empleado
    {
        $empleado->update($data);
        return $empleado->fresh(['sucursal', 'user']);
    }

    public function delete(Empleado $empleado): void
    {
        $empleado->delete();
    }
}