<?php

namespace App\Services;

use App\Models\MenuCategoria;
use App\Repositories\MenuCategoriaRepository;
use Illuminate\Database\Eloquent\Collection;

class MenuCategoriaService
{
    public function __construct(private MenuCategoriaRepository $repository) {}

    public function getAll(bool $withProductos = false): Collection
    {
        return $withProductos
            ? $this->repository->allWithProductos()
            : $this->repository->all();
    }

    public function getById(int $id): MenuCategoria
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): MenuCategoria
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): MenuCategoria
    {
        $categoria = $this->repository->findOrFail($id);
        return $this->repository->update($categoria, $data);
    }

    public function delete(int $id): void
    {
        $categoria = $this->repository->findOrFail($id);
        $this->repository->delete($categoria);
    }
}