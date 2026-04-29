<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SucursalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function __construct(private SucursalService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:100',
            'direccion' => 'nullable|string|max:200',
            'ciudad'    => 'nullable|string|max:100',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:100',
            'activo'    => 'boolean',
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nombre'    => 'sometimes|string|max:100',
            'direccion' => 'nullable|string|max:200',
            'ciudad'    => 'nullable|string|max:100',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:100',
            'activo'    => 'boolean',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Sucursal eliminada correctamente.']);
    }
}