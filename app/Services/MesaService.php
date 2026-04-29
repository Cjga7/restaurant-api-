<?php

namespace App\Services;

use App\Models\Mesa;
use App\Repositories\MesaRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class MesaService
{
    public function __construct(private MesaRepository $repository) {}

    public function getAll(?string $estado = null, ?int $sucursalId = null): Collection
    {
        return $this->repository->all($estado, $sucursalId);
    }

    public function getById(int $id): Mesa
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Mesa
    {
        // Generar código QR único para la mesa
        $data['qr_code'] = Str::uuid();
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Mesa
    {
        $mesa = $this->repository->findOrFail($id);
        return $this->repository->update($mesa, $data);
    }

    public function delete(int $id): void
    {
        $mesa = $this->repository->findOrFail($id);
        $this->repository->delete($mesa);
    }

    public function cambiarEstado(int $id, string $estado): Mesa
    {
        $mesa = $this->repository->findOrFail($id);
        return $this->repository->cambiarEstado($mesa, $estado);
    }
}