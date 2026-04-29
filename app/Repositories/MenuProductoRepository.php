<?php

namespace App\Repositories;

use App\Models\MenuProducto;
use Illuminate\Database\Eloquent\Collection;

class MenuProductoRepository
{
    public function allByCategoria(int $categoriaId): Collection
    {
        return MenuProducto::with('categoria')
                           ->where('categoria_id', $categoriaId)
                           ->orderBy('nombre')
                           ->get();
    }

    public function all(): Collection
    {
        return MenuProducto::with('categoria')->orderBy('nombre')->get();
    }

    public function findOrFail(int $id): MenuProducto
    {
        return MenuProducto::with('categoria')->findOrFail($id);
    }

    public function create(array $data): MenuProducto
    {
        return MenuProducto::create($data);
    }

    public function update(MenuProducto $producto, array $data): MenuProducto
    {
        $producto->update($data);
        return $producto->fresh('categoria');
    }

    public function delete(MenuProducto $producto): void
    {
        $producto->delete();
    }

    public function syncSucursal(MenuProducto $producto, int $sucursalId, array $pivotData): void
    {
        $producto->sucursales()->syncWithoutDetaching([
            $sucursalId => $pivotData,
        ]);
    }

    public function getParaSucursal(int $sucursalId): Collection
    {
        return MenuProducto::with('categoria')
            ->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                  ->where('disponible', true);
            })
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }
}