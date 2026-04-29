<?php

namespace App\Repositories;

use App\Models\InventarioItem;
use Illuminate\Database\Eloquent\Collection;

class InventarioItemRepository
{
    public function all(?string $categoria = null): Collection
    {
        return InventarioItem::with('productoMenu')
            ->when($categoria, fn($q, $v) => $q->where('categoria', $v))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function findOrFail(int $id): InventarioItem
    {
        return InventarioItem::with('productoMenu')->findOrFail($id);
    }

    public function create(array $data): InventarioItem
    {
        return InventarioItem::create($data);
    }

    public function update(InventarioItem $item, array $data): InventarioItem
    {
        $item->update($data);
        return $item->fresh('productoMenu');
    }

    public function delete(InventarioItem $item): void
    {
        $item->delete();
    }
}