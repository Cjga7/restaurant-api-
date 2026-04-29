<?php

namespace App\Services;

use App\Models\Sucursal;
use App\Repositories\SucursalRepository;
use Illuminate\Database\Eloquent\Collection;

class SucursalService
{
    public function __construct(private SucursalRepository $repository) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getById(int $id): Sucursal
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Sucursal
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Sucursal
    {
        $sucursal = $this->repository->findOrFail($id);
        return $this->repository->update($sucursal, $data);
    }

    public function delete(int $id): void
    {
        $sucursal = $this->repository->findOrFail($id);
        $this->repository->delete($sucursal);
    }
}