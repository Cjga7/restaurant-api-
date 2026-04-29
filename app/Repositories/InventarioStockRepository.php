<?php

namespace App\Repositories;

use App\Models\InventarioStock;
use Illuminate\Database\Eloquent\Collection;

class InventarioStockRepository
{
    public function all(array $filters = []): Collection
    {
        return InventarioStock::with(['item', 'sucursal'])
            ->when($filters['sucursal_id'] ?? null, fn($q, $v) => $q->where('sucursal_id', $v))
            ->when($filters['categoria'] ?? null, fn($q, $v) => $q->whereHas('item', fn($iq) => $iq->where('categoria', $v)))
            ->when($filters['alerta'] ?? null, fn($q) => $q->whereColumn('stock_actual', '<=', 'stock_minimo'))
            ->get()
            ->sortBy('item.nombre')
            ->values();
    }

    public function findOrCreate(int $sucursalId, int $itemId, array $data = []): InventarioStock
    {
        return InventarioStock::firstOrCreate(
            ['sucursal_id' => $sucursalId, 'item_id' => $itemId],
            array_merge(['stock_actual' => 0, 'stock_minimo' => 0], $data)
        );
    }

    public function findOrFail(int $id): InventarioStock
    {
        return InventarioStock::with(['item', 'sucursal'])->findOrFail($id);
    }

    public function update(InventarioStock $stock, array $data): InventarioStock
    {
        $stock->update($data);
        return $stock->fresh(['item', 'sucursal']);
    }
}