<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Pedido;
use App\Repositories\CajaSesionRepository;
use App\Repositories\PagoRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoService
{
    public function __construct(
        private PagoRepository $repository,
        private CajaSesionRepository $sesionRepo,
        private PedidoService $pedidoService,
    ) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function procesar(array $data): Pago
    {
        return DB::transaction(function () use ($data) {
            $pedido = Pedido::findOrFail($data['pedido_id']);

            if ($this->repository->findByPedido($pedido->id)) {
                throw new \Exception('Este pedido ya fue cobrado.');
            }

            if ($pedido->estado === 'cancelado') {
                throw new \Exception('No se puede cobrar un pedido cancelado.');
            }

            // Buscar sesión de caja abierta del cajero actual
            $sesion = $this->sesionRepo->sesionAbierta(Auth::id(), $pedido->sucursal_id);

            // Validar montos según método
            $montos = $this->calcularMontos($data, $pedido->total);

            $pago = $this->repository->create([
                'pedido_id'       => $pedido->id,
                'caja_sesion_id'  => $sesion?->id,
                'cajero_id'       => Auth::id(),
                'metodo'          => $data['metodo'],
                'monto_total'     => $pedido->total,
                'monto_efectivo'  => $montos['efectivo'],
                'monto_tarjeta'   => $montos['tarjeta'],
                'monto_qr'        => $montos['qr'],
                'monto_recibido'  => $data['monto_recibido'] ?? null,
                'cambio'          => $montos['cambio'],
                'referencia'      => $data['referencia'] ?? null,
                'notas'           => $data['notas'] ?? null,
            ]);

            // Cambiar estado del pedido a pagado (esto también libera la mesa y descuenta stock si hay recetas)
            $this->pedidoService->cambiarEstado($pedido->id, 'pagado');

            return $pago->fresh(['pedido', 'cajero', 'sesion']);
        });
    }

    private function calcularMontos(array $data, float $total): array
    {
        $efectivo = 0;
        $tarjeta  = 0;
        $qr       = 0;
        $cambio   = 0;

        switch ($data['metodo']) {
            case 'efectivo':
                $recibido = (float) ($data['monto_recibido'] ?? $total);
                if ($recibido < $total) {
                    throw new \Exception("Monto recibido ({$recibido}) es menor que el total ({$total}).");
                }
                $efectivo = $total;
                $cambio   = $recibido - $total;
                break;

            case 'tarjeta':
                $tarjeta = $total;
                break;

            case 'qr':
            case 'transferencia':
                $qr = $total;
                break;

            case 'mixto':
                $efectivo = (float) ($data['monto_efectivo'] ?? 0);
                $tarjeta  = (float) ($data['monto_tarjeta']  ?? 0);
                $qr       = (float) ($data['monto_qr']       ?? 0);
                $sumaPagado = $efectivo + $tarjeta + $qr;

                if (abs($sumaPagado - $total) > 0.01) {
                    throw new \Exception("La suma de los montos ({$sumaPagado}) no coincide con el total ({$total}).");
                }
                break;
        }

        return compact('efectivo', 'tarjeta', 'qr', 'cambio');
    }
}