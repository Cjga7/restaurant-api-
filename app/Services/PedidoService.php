<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\MenuProducto;
use App\Models\Pedido;
use App\Repositories\PedidoRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\PedidoActualizado;

class PedidoService
{
    public function __construct(private PedidoRepository $repository) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function getById(int $id): Pedido
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Pedido
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $data['numero']  = $this->repository->generarNumero($data['sucursal_id']);
            $data['mozo_id'] = Auth::id();

            $pedido = $this->repository->create($data);

            foreach ($items as $item) {
                $this->agregarItemInterno($pedido, $item);
            }

            $pedido->recalcularTotales();

            // Si es pedido de mesa, marcar mesa como ocupada
            if ($pedido->mesa_id) {
                Mesa::withoutGlobalScopes()
                    ->where('id', $pedido->mesa_id)
                    ->update(['estado' => 'ocupada']);
            }

            $pedido = $pedido->fresh(['mesa', 'mozo', 'sucursal', 'items']);

            // 🔔 Broadcast en tiempo real
            broadcast(new PedidoActualizado($pedido, 'creado'));

            return $pedido;
        });
    }

    public function update(int $id, array $data): Pedido
    {
        $pedido = $this->repository->findOrFail($id);
        return $this->repository->update($pedido, $data);
    }

    public function delete(int $id): void
    {
        $pedido = $this->repository->findOrFail($id);

        // Liberar mesa si estaba ocupada
        if ($pedido->mesa_id) {
            Mesa::withoutGlobalScopes()
                ->where('id', $pedido->mesa_id)
                ->update(['estado' => 'disponible']);
        }

        $this->repository->delete($pedido);
    }

    public function cambiarEstado(int $id, string $estado): Pedido
    {
        return DB::transaction(function () use ($id, $estado) {
            $pedido = $this->repository->findOrFail($id);
            $estadoAnterior = $pedido->estado;

            $pedido = $this->repository->update($pedido, ['estado' => $estado]);

            // Descontar stock cuando el pedido se paga
            if ($estado === 'pagado' && $estadoAnterior !== 'pagado') {
                $this->descontarStockDeRecetas($pedido);
            }

            // Liberar mesa si el pedido se paga o cancela
            if (in_array($estado, ['pagado', 'cancelado']) && $pedido->mesa_id) {
                Mesa::withoutGlobalScopes()
                    ->where('id', $pedido->mesa_id)
                    ->update(['estado' => 'disponible']);
            }

            $pedido = $pedido->fresh(['mesa', 'mozo', 'sucursal', 'items']);

            // 🔔 Broadcast en tiempo real
            broadcast(new PedidoActualizado($pedido, 'estado_cambiado'));

            return $pedido;
        });
    }

    private function descontarStockDeRecetas(Pedido $pedido): void
    {
        $stockService = app(\App\Services\InventarioStockService::class);
        $pedido->load('items.producto.recetas.item');

        foreach ($pedido->items as $item) {
            $recetas = $item->producto?->recetas ?? collect();

            foreach ($recetas as $receta) {
                $cantidadNecesaria = $receta->cantidad * $item->cantidad;

                try {
                    $stockService->registrarMovimiento([
                        'sucursal_id' => $pedido->sucursal_id,
                        'item_id'     => $receta->item_id,
                        'tipo'        => 'salida',
                        'cantidad'    => $cantidadNecesaria,
                        'motivo'      => 'consumo',
                        'notas'       => "Pedido #{$pedido->numero} · {$item->producto_nombre} x{$item->cantidad}",
                    ]);
                } catch (\Exception $e) {
                    Log::warning("No se pudo descontar stock: {$e->getMessage()}");
                }
            }
        }
    }

    public function agregarItem(int $pedidoId, array $itemData): Pedido
    {
        return DB::transaction(function () use ($pedidoId, $itemData) {
            $pedido = $this->repository->findOrFail($pedidoId);
            $this->agregarItemInterno($pedido, $itemData);
            $pedido->recalcularTotales();

            $pedido = $pedido->fresh(['mesa', 'mozo', 'sucursal', 'items']);

            // 🔔 Broadcast
            broadcast(new PedidoActualizado($pedido, 'actualizado'));

            return $pedido;
        });
    }

    private function agregarItemInterno(Pedido $pedido, array $itemData): void
    {
        $producto = MenuProducto::findOrFail($itemData['producto_id']);

        $precio   = $producto->precioParaSucursal($pedido->sucursal_id);
        $cantidad = $itemData['cantidad'] ?? 1;

        $this->repository->addItem($pedido, [
            'producto_id'     => $producto->id,
            'producto_nombre' => $producto->nombre,
            'precio_unitario' => $precio,
            'cantidad'        => $cantidad,
            'subtotal'        => $precio * $cantidad,
        ]);
    }

    public function actualizarCantidadItem(int $pedidoId, int $itemId, int $cantidad): Pedido
    {
        return DB::transaction(function () use ($pedidoId, $itemId, $cantidad) {
            $item = $this->repository->findItem($itemId);

            $this->repository->updateItem($item, [
                'cantidad' => $cantidad,
                'subtotal' => $item->precio_unitario * $cantidad,
            ]);

            $pedido = $this->repository->findOrFail($pedidoId);
            $pedido->recalcularTotales();

            $pedido = $pedido->fresh(['mesa', 'mozo', 'sucursal', 'items']);

            // 🔔 Broadcast
            broadcast(new PedidoActualizado($pedido, 'actualizado'));

            return $pedido;
        });
    }

    public function eliminarItem(int $pedidoId, int $itemId): Pedido
    {
        return DB::transaction(function () use ($pedidoId, $itemId) {
            $item = $this->repository->findItem($itemId);
            $this->repository->deleteItem($item);

            $pedido = $this->repository->findOrFail($pedidoId);
            $pedido->recalcularTotales();

            $pedido = $pedido->fresh(['mesa', 'mozo', 'sucursal', 'items']);

            // 🔔 Broadcast
            broadcast(new PedidoActualizado($pedido, 'actualizado'));

            return $pedido;
        });
    }
}