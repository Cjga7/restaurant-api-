<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventarioItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioItemController extends Controller
{
    public function __construct(private InventarioItemService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->getAll($request->query('categoria')));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'descripcion'      => 'nullable|string',
            'categoria'        => 'required|in:ingrediente,bebida,empaque,otro',
            'unidad'           => 'required|in:kg,g,litro,ml,unidad,caja,paquete,botella,lata',
            'producto_menu_id' => 'nullable|exists:menu_productos,id',
            'activo'           => 'boolean',
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nombre'           => 'sometimes|string|max:100',
            'descripcion'      => 'nullable|string',
            'categoria'        => 'sometimes|in:ingrediente,bebida,empaque,otro',
            'unidad'           => 'sometimes|in:kg,g,litro,ml,unidad,caja,paquete,botella,lata',
            'producto_menu_id' => 'nullable|exists:menu_productos,id',
            'activo'           => 'boolean',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Item eliminado correctamente.']);
    }
}