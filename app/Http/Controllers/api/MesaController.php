<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    public function __construct(private MesaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $sucursalId = $request->query('sucursal_id') ? (int) $request->query('sucursal_id') : null;
        return response()->json($this->service->getAll($request->query('estado'), $sucursalId));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'numero'      => 'required|string|max:20',
            'capacidad'   => 'integer|min:1|max:20',
            'ubicacion'   => 'nullable|string|max:100',
            'estado'      => 'in:disponible,ocupada,reservada,inactiva',
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'numero'    => 'sometimes|string|max:20',
            'capacidad' => 'integer|min:1|max:20',
            'ubicacion' => 'nullable|string|max:100',
            'estado'    => 'in:disponible,ocupada,reservada,inactiva',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Mesa eliminada correctamente.']);
    }

    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'estado' => 'required|in:disponible,ocupada,reservada,inactiva',
        ]);

        return response()->json($this->service->cambiarEstado($id, $data['estado']));
    }
}