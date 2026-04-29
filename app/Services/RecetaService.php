<?php

namespace App\Services;

use App\Models\Receta;
use App\Repositories\RecetaRepository;
use Illuminate\Database\Eloquent\Collection;

class RecetaService
{
    public function __construct(private RecetaRepository $repository) {}

    public function getByProducto(int $productoId): Collection
    {
        return $this->repository->getByProducto($productoId);
    }

    public function sync(int $productoId, array $items): Collection
    {
        $this->repository->syncReceta($productoId, $items);
        return $this->repository->getByProducto($productoId);
    }

    public function addItem(array $data): Receta
    {
        return $this->repository->create($data);
    }

    public function updateItem(int $id, array $data): Receta
    {
        $receta = $this->repository->findOrFail($id);
        return $this->repository->update($receta, $data);
    }

    public function deleteItem(int $id): void
    {
        $receta = $this->repository->findOrFail($id);
        $this->repository->delete($receta);
    }
}