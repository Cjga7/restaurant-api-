<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReservaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function __construct(private ReservaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['estado', 'sucursal_id', 'fecha']);
        if (isset($filters['sucursal_id'])) $filters['sucursal_id'] = (int) $filters['sucursal_id'];

        return response()->json($this->service->getAll($filters));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id'       => 'required|exists:sucursales,id',
            'mesa_id'           => 'nullable|exists:mesas,id',
            'cliente_nombre'    => 'required|string|max:100',
            'cliente_telefono'  => 'required|string|max:20',
            'cliente_email'     => 'nullable|email|max:100',
            'cantidad_personas' => 'required|integer|min:1|max:50',
            'fecha_hora'        => 'required|date|after_or_equal:today',
            'estado'            => 'in:pendiente,confirmada,cancelada,completada,no_asistio',
            'notas'             => 'nullable|string',
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'mesa_id'           => 'nullable|exists:mesas,id',
            'cliente_nombre'    => 'sometimes|string|max:100',
            'cliente_telefono'  => 'sometimes|string|max:20',
            'cliente_email'     => 'nullable|email|max:100',
            'cantidad_personas' => 'sometimes|integer|min:1|max:50',
            'fecha_hora'        => 'sometimes|date',
            'estado'            => 'in:pendiente,confirmada,cancelada,completada,no_asistio',
            'notas'             => 'nullable|string',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Reserva eliminada correctamente.']);
    }

    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'estado' => 'required|in:pendiente,confirmada,cancelada,completada,no_asistio',
        ]);

        return response()->json($this->service->cambiarEstado($id, $data['estado']));
    }
    public function clienteLlego(int $id): JsonResponse
{
    try {
        $resultado = $this->service->clienteLlego($id);
        return response()->json($resultado, 201);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
}
}