<?php

namespace App\Repositories;

use App\Models\Reserva;
use Illuminate\Database\Eloquent\Collection;

class ReservaRepository
{
    public function all(array $filters = []): Collection
    {
        return Reserva::with(['sucursal', 'mesa', 'registradoPor'])
            ->when($filters['estado'] ?? null,    fn($q, $v) => $q->where('estado', $v))
            ->when($filters['sucursal_id'] ?? null, fn($q, $v) => $q->where('sucursal_id', $v))
            ->when($filters['fecha'] ?? null,     fn($q, $v) => $q->whereDate('fecha_hora', $v))
            ->orderBy('fecha_hora', 'desc')
            ->get();
    }

    public function findOrFail(int $id): Reserva
    {
        return Reserva::with(['sucursal', 'mesa', 'registradoPor'])->findOrFail($id);
    }

    public function create(array $data): Reserva
    {
        return Reserva::create($data);
    }

    public function update(Reserva $reserva, array $data): Reserva
    {
        $reserva->update($data);
        return $reserva->fresh(['sucursal', 'mesa', 'registradoPor']);
    }

    public function delete(Reserva $reserva): void
    {
        $reserva->delete();
    }

    public function cambiarEstado(Reserva $reserva, string $estado): Reserva
    {
        $reserva->update(['estado' => $estado]);
        return $reserva->fresh(['sucursal', 'mesa', 'registradoPor']);
    }
}