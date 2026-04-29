<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MenuProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuProductoController extends Controller
{
    public function __construct(private MenuProductoService $service) {}

    public function index(Request $request): JsonResponse
    {
        $categoriaId = $request->query('categoria_id');
        return response()->json($this->service->getAll($categoriaId));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

   public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'categoria_id' => 'required|exists:menu_categorias,id',
        'nombre'       => 'required|string|max:150',
        'descripcion'  => 'nullable|string',
        'precio_base'  => 'required|numeric|min:0',
        'activo'       => 'boolean',
        'imagen'       => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('imagen')) {
        $data['imagen'] = $request->file('imagen')->store('menu', 'public');
    }

    return response()->json($this->service->create($data), 201);
}

public function update(Request $request, int $id): JsonResponse
{
    $data = $request->validate([
        'categoria_id' => 'sometimes|exists:menu_categorias,id',
        'nombre'       => 'sometimes|string|max:150',
        'descripcion'  => 'nullable|string',
        'precio_base'  => 'sometimes|numeric|min:0',
        'activo'       => 'boolean',
        'imagen'       => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('imagen')) {
        $data['imagen'] = $request->file('imagen')->store('menu', 'public');
    }

    return response()->json($this->service->update($id, $data));
}

    public function destroy(int $id): JsonResponse
{
    try {
        $this->service->delete($id);
        return response()->json(['message' => 'Producto eliminado correctamente.']);
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->getCode() === '23000') {
            return response()->json([
                'message' => 'No se puede eliminar: el producto tiene pedidos asociados.'
            ], 409);
        }
        throw $e;
    }
}

    // Configura precio y disponibilidad de un producto en una sucursal específica
    public function configurarSucursal(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'precio'      => 'nullable|numeric|min:0',
            'disponible'  => 'boolean',
        ]);

        $producto = $this->service->configurarEnSucursal($id, $data['sucursal_id'], $data);
        return response()->json($producto);
    }

    public function paraSucursal(int $sucursalId): JsonResponse
    {
        return response()->json($this->service->getParaSucursal($sucursalId));
    }
}