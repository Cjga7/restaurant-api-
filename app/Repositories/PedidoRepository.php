<?php

namespace App\Repositories;

use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Database\Eloquent\Collection;

class PedidoRepository
{
    public function all(array $filters = []): Collection
    {
        return Pedido::with(['mesa', 'mozo', 'sucursal', 'items'])
            ->when($filters['estado'] ?? null,      fn($q, $v) => $q->where('estado', $v))
            ->when($filters['tipo'] ?? null,        fn($q, $v) => $q->where('tipo', $v))
            ->when($filters['sucursal_id'] ?? null, fn($q, $v) => $q->where('sucursal_id', $v))
            ->when($filters['mesa_id'] ?? null,     fn($q, $v) => $q->where('mesa_id', $v))
            ->when($filters['activos'] ?? null,     fn($q) => $q->whereNotIn('estado', ['pagado', 'cancelado']))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findOrFail(int $id): Pedido
    {
        return Pedido::with(['mesa', 'mozo', 'sucursal', 'items.producto'])->findOrFail($id);
    }

    public function create(array $data): Pedido
    {
        return Pedido::create($data);
    }

    public function update(Pedido $pedido, array $data): Pedido
    {
        $pedido->update($data);
        return $pedido->fresh(['mesa', 'mozo', 'sucursal', 'items']);
    }

    public function delete(Pedido $pedido): void
    {
        $pedido->delete();
    }

    public function addItem(Pedido $pedido, array $itemData): PedidoItem
    {
        return $pedido->items()->create($itemData);
    }

    public function updateItem(PedidoItem $item, array $data): PedidoItem
    {
        $item->update($data);
        return $item->fresh();
    }

    public function deleteItem(PedidoItem $item): void
    {
        $item->delete();
    }

    public function findItem(int $itemId): PedidoItem
    {
        return PedidoItem::findOrFail($itemId);
    }

    public function generarNumero(int $sucursalId): string
    {
        $count = Pedido::withoutGlobalScopes()
                       ->where('sucursal_id', $sucursalId)
                       ->whereDate('created_at', today())
                       ->count();

        return date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}