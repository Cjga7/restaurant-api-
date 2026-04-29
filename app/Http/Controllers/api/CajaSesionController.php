<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CajaSesionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CajaSesionController extends Controller
{
    public function __construct(private CajaSesionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['estado', 'sucursal_id', 'cajero_id']);
        foreach (['sucursal_id', 'cajero_id'] as $k) {
            if (isset($filters[$k])) $filters[$k] = (int) $filters[$k];
        }
        return response()->json($this->service->getAll($filters));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

   public function miSesion(Request $request): JsonResponse
{
    $sucursalId = (int) $request->query('sucursal_id');
    $sesion = $this->service->miSesionAbierta($sucursalId);

    return $sesion
        ? response()->json($sesion)
        : response()->json(null);
}

    public function abrir(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id'    => 'required|exists:sucursales,id',
            'monto_inicial'  => 'required|numeric|min:0',
            'notas_apertura' => 'nullable|string',
        ]);

        try {
            return response()->json($this->service->abrir($data), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cerrar(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'monto_real'   => 'required|numeric|min:0',
            'notas_cierre' => 'nullable|string',
        ]);

        try {
            return response()->json($this->service->cerrar($id, $data));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function activas(Request $request): JsonResponse
{
    $sucursalId = $request->query('sucursal_id') ? (int) $request->query('sucursal_id') : null;
    return response()->json($this->service->getSesionesActivas($sucursalId));
}
}