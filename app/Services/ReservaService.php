<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\Reserva;
use App\Repositories\ReservaRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class ReservaService
{
    public function __construct(private ReservaRepository $repository) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function getById(int $id): Reserva
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Reserva
    {
        $data['registrada_por'] = Auth::id();

        $reserva = $this->repository->create($data);

        // Si se asigna mesa y la reserva está confirmada, marcar mesa como reservada
        if ($reserva->mesa_id && $reserva->estado === 'confirmada') {
            Mesa::withoutGlobalScopes()->where('id', $reserva->mesa_id)->update(['estado' => 'reservada']);
        }

        return $reserva;
    }

    public function update(int $id, array $data): Reserva
    {
        $reserva = $this->repository->findOrFail($id);
        return $this->repository->update($reserva, $data);
    }

    public function delete(int $id): void
    {
        $reserva = $this->repository->findOrFail($id);
        $this->repository->delete($reserva);
    }

    public function cambiarEstado(int $id, string $estado): Reserva
    {
        $reserva = $this->repository->findOrFail($id);
        $reserva  = $this->repository->cambiarEstado($reserva, $estado);

        // Sincronizar estado de la mesa asociada
        if ($reserva->mesa_id) {
            $mesaEstado = match ($estado) {
                'confirmada' => 'reservada',
                'completada', 'cancelada', 'no_asistio' => 'disponible',
                default => null,
            };
            if ($mesaEstado) {
                Mesa::withoutGlobalScopes()->where('id', $reserva->mesa_id)->update(['estado' => $mesaEstado]);
            }
        }

        return $reserva;
    }
    public function clienteLlego(int $reservaId): array
{
    return DB::transaction(function () use ($reservaId) {
        $reserva = $this->repository->findOrFail($reservaId);

        // Validaciones
        if (!$reserva->mesa_id) {
            throw new \Exception('Esta reserva no tiene mesa asignada. Asigná una mesa antes de continuar.');
        }
        if (!in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            throw new \Exception('Solo se puede registrar la llegada de reservas pendientes o confirmadas.');
        }

        // 1. Crear pedido vinculado a la mesa de la reserva
        $pedidoService = app(\App\Services\PedidoService::class);
        $pedido = $pedidoService->create([
            'sucursal_id'      => $reserva->sucursal_id,
            'mesa_id'          => $reserva->mesa_id,
            'tipo'             => 'mesa',
            'cliente_nombre'   => $reserva->cliente_nombre,
            'cliente_telefono' => $reserva->cliente_telefono,
            'notas'            => "Reserva #{$reserva->id} · {$reserva->cantidad_personas} personas",
        ]);

        // 2. Marcar la reserva como completada
        $reserva = $this->repository->cambiarEstado($reserva, 'completada');

        return [
            'reserva' => $reserva,
            'pedido'  => $pedido,
        ];
    });
}
}