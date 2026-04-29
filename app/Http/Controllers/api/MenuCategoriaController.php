<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MenuCategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuCategoriaController extends Controller
{
    public function __construct(private MenuCategoriaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $withProductos = $request->boolean('with_productos');
        return response()->json($this->service->getAll($withProductos));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'orden'       => 'integer|min:0',
            'activo'      => 'boolean',
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'orden'       => 'integer|min:0',
            'activo'      => 'boolean',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Categoría eliminada correctamente.']);
    }
}