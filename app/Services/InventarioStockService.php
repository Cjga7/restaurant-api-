<?php

namespace App\Services;

use App\Models\InventarioStock;
use App\Repositories\InventarioStockRepository;
use App\Repositories\MovimientoInventarioRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventarioStockService
{
    public function __construct(
        private InventarioStockRepository $stockRepo,
        private MovimientoInventarioRepository $movRepo
    ) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->stockRepo->all($filters);
    }

    public function getById(int $id): InventarioStock
    {
        return $this->stockRepo->findOrFail($id);
    }

    public function actualizarUmbrales(int $id, array $data): InventarioStock
    {
        $stock = $this->stockRepo->findOrFail($id);
        return $this->stockRepo->update($stock, [
            'stock_minimo'  => $data['stock_minimo']  ?? $stock->stock_minimo,
            'stock_ideal'   => $data['stock_ideal']   ?? $stock->stock_ideal,
            'precio_compra' => $data['precio_compra'] ?? $stock->precio_compra,
        ]);
    }

    // Registra un movimiento y actualiza el stock en transacción
    public function registrarMovimiento(array $data): InventarioStock
    {
        return DB::transaction(function () use ($data) {
            $stock = $this->stockRepo->findOrCreate(
                $data['sucursal_id'],
                $data['item_id']
            );

            $stockAnterior = $stock->stock_actual;
            $cantidad      = (float) $data['cantidad'];

            // Calcular nuevo stock según tipo
            $stockNuevo = match ($data['tipo']) {
                'entrada' => $stockAnterior + $cantidad,
                'salida'  => max(0, $stockAnterior - $cantidad),
                'ajuste'  => $cantidad, // el ajuste setea el stock directamente
                default   => $stockAnterior,
            };

            // Actualizar stock
            $this->stockRepo->update($stock, [
                'stock_actual'  => $stockNuevo,
                'precio_compra' => $data['precio_unitario'] ?? $stock->precio_compra,
            ]);

            // Registrar movimiento
            $this->movRepo->create([
                'sucursal_id'     => $data['sucursal_id'],
                'item_id'         => $data['item_id'],
                'user_id'         => Auth::id(),
                'tipo'            => $data['tipo'],
                'cantidad'        => $data['tipo'] === 'ajuste' ? abs($stockNuevo - $stockAnterior) : $cantidad,
                'stock_anterior'  => $stockAnterior,
                'stock_nuevo'     => $stockNuevo,
                'precio_unitario' => $data['precio_unitario'] ?? null,
                'motivo'          => $data['motivo'] ?? null,
                'notas'           => $data['notas'] ?? null,
            ]);

            return $stock->fresh(['item', 'sucursal']);
        });
    }
    public function transferirEntreSucursales(array $data): array
{
    return DB::transaction(function () use ($data) {
        $origenStock = $this->stockRepo->findOrCreate(
            $data['sucursal_origen_id'],
            $data['item_id']
        );

        if ($origenStock->stock_actual < $data['cantidad']) {
            throw new \Exception(
                "Stock insuficiente en sucursal origen. Solo hay {$origenStock->stock_actual}."
            );
        }

        $notas = "Transferencia: " . ($data['notas'] ?? '');

        // Salida de origen
        $origen = $this->registrarMovimiento([
            'sucursal_id' => $data['sucursal_origen_id'],
            'item_id'     => $data['item_id'],
            'tipo'        => 'salida',
            'cantidad'    => $data['cantidad'],
            'motivo'      => 'transferencia',
            'notas'       => "→ A sucursal ID {$data['sucursal_destino_id']}. {$notas}",
        ]);

        // Entrada en destino
        $destino = $this->registrarMovimiento([
            'sucursal_id' => $data['sucursal_destino_id'],
            'item_id'     => $data['item_id'],
            'tipo'        => 'entrada',
            'cantidad'    => $data['cantidad'],
            'precio_unitario' => $origenStock->precio_compra,
            'motivo'      => 'transferencia',
            'notas'       => "← Desde sucursal ID {$data['sucursal_origen_id']}. {$notas}",
        ]);

        return [
            'origen'   => $origen,
            'destino'  => $destino,
            'cantidad' => $data['cantidad'],
        ];
    });
}
}