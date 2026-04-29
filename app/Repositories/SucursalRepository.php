<?php

namespace App\Repositories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Collection;

class SucursalRepository
{
    public function all(): Collection
    {
        return Sucursal::orderBy('nombre')->get();
    }

    public function findOrFail(int $id): Sucursal
    {
        return Sucursal::findOrFail($id);
    }

    public function create(array $data): Sucursal
    {
        return Sucursal::create($data);
    }

    public function update(Sucursal $sucursal, array $data): Sucursal
    {
        $sucursal->update($data);
        return $sucursal->fresh();
    }

    public function delete(Sucursal $sucursal): void
    {
        $sucursal->delete();
    }
}