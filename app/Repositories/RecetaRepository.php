<?php

namespace App\Repositories;

use App\Models\Receta;
use Illuminate\Database\Eloquent\Collection;

class RecetaRepository
{
    public function getByProducto(int $productoId): Collection
    {
        return Receta::with('item')
                     ->where('producto_id', $productoId)
                     ->get();
    }

    public function findOrFail(int $id): Receta
    {
        return Receta::with('item')->findOrFail($id);
    }

    public function create(array $data): Receta
    {
        return Receta::create($data);
    }

    public function update(Receta $receta, array $data): Receta
    {
        $receta->update($data);
        return $receta->fresh('item');
    }

    public function delete(Receta $receta): void
    {
        $receta->delete();
    }

    public function syncReceta(int $productoId, array $items): void
    {
        // items = [ ['item_id' => 1, 'cantidad' => 0.15], ['item_id' => 2, 'cantidad' => 1], ... ]
        Receta::where('producto_id', $productoId)->delete();
        foreach ($items as $item) {
            Receta::create([
                'producto_id' => $productoId,
                'item_id'     => $item['item_id'],
                'cantidad'    => $item['cantidad'],
                'notas'       => $item['notas'] ?? null,
            ]);
        }
    }
}