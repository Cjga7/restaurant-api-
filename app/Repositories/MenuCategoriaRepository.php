<?php

namespace App\Repositories;

use App\Models\MenuCategoria;
use Illuminate\Database\Eloquent\Collection;

class MenuCategoriaRepository
{
    public function all(): Collection
    {
        return MenuCategoria::orderBy('orden')->orderBy('nombre')->get();
    }

    public function allWithProductos(): Collection
    {
        return MenuCategoria::with('productos')
                            ->orderBy('orden')
                            ->orderBy('nombre')
                            ->get();
    }

    public function findOrFail(int $id): MenuCategoria
    {
        return MenuCategoria::findOrFail($id);
    }

    public function create(array $data): MenuCategoria
    {
        return MenuCategoria::create($data);
    }

    public function update(MenuCategoria $categoria, array $data): MenuCategoria
    {
        $categoria->update($data);
        return $categoria->fresh();
    }

    public function delete(MenuCategoria $categoria): void
    {
        $categoria->delete();
    }
}