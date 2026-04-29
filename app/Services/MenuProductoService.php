<?php

namespace App\Services;

use App\Models\MenuProducto;
use App\Repositories\MenuProductoRepository;
use Illuminate\Database\Eloquent\Collection;

class MenuProductoService
{
    public function __construct(private MenuProductoRepository $repository) {}

    public function getAll(?int $categoriaId = null): Collection
    {
        return $categoriaId
            ? $this->repository->allByCategoria($categoriaId)
            : $this->repository->all();
    }

    public function getById(int $id): MenuProducto
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): MenuProducto
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): MenuProducto
    {
        $producto = $this->repository->findOrFail($id);
        return $this->repository->update($producto, $data);
    }

    public function delete(int $id): void
    {
        $producto = $this->repository->findOrFail($id);
        $this->repository->delete($producto);
    }

    public function configurarEnSucursal(int $productoId, int $sucursalId, array $data): MenuProducto
    {
        $producto = $this->repository->findOrFail($productoId);
        $this->repository->syncSucursal($producto, $sucursalId, [
            'precio'     => $data['precio'] ?? null,
            'disponible' => $data['disponible'] ?? true,
        ]);
        return $producto;
    }

    public function getParaSucursal(int $sucursalId): Collection
    {
        return $this->repository->getParaSucursal($sucursalId);
    }
}