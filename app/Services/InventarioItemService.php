<?php

namespace App\Services;

use App\Models\InventarioItem;
use App\Repositories\InventarioItemRepository;
use Illuminate\Database\Eloquent\Collection;

class InventarioItemService
{
    public function __construct(private InventarioItemRepository $repository) {}

    public function getAll(?string $categoria = null): Collection
    {
        return $this->repository->all($categoria);
    }

    public function getById(int $id): InventarioItem
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): InventarioItem
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): InventarioItem
    {
        $item = $this->repository->findOrFail($id);
        return $this->repository->update($item, $data);
    }

    public function delete(int $id): void
    {
        $item = $this->repository->findOrFail($id);
        $this->repository->delete($item);
    }
}