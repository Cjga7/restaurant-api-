<?php

namespace App\Services;

use App\Repositories\MovimientoInventarioRepository;
use Illuminate\Database\Eloquent\Collection;

class MovimientoInventarioService
{
    public function __construct(private MovimientoInventarioRepository $repository) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }
}