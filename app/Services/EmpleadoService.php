<?php

namespace App\Services;

use App\Models\Empleado;
use App\Repositories\EmpleadoRepository;
use Illuminate\Database\Eloquent\Collection;

class EmpleadoService
{
    public function __construct(private EmpleadoRepository $repository) {}

    public function getAll(?string $cargo = null, ?int $sucursalId = null): Collection
    {
        return $this->repository->all($cargo, $sucursalId);
    }

    public function getById(int $id): Empleado
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Empleado
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Empleado
    {
        $empleado = $this->repository->findOrFail($id);
        return $this->repository->update($empleado, $data);
    }

    public function delete(int $id): void
    {
        $empleado = $this->repository->findOrFail($id);
        $this->repository->delete($empleado);
    }
}