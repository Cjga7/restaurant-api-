<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function __construct(private PagoService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['caja_sesion_id', 'metodo', 'desde', 'hasta', 'limit']);
        foreach (['caja_sesion_id', 'limit'] as $k) {
            if (isset($filters[$k])) $filters[$k] = (int) $filters[$k];
        }
        return response()->json($this->service->getAll($filters));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pedido_id'      => 'required|exists:pedidos,id',
            'metodo'         => 'required|in:efectivo,tarjeta,qr,transferencia,mixto',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'monto_tarjeta'  => 'nullable|numeric|min:0',
            'monto_qr'       => 'nullable|numeric|min:0',
            'monto_recibido' => 'nullable|numeric|min:0',
            'referencia'     => 'nullable|string|max:100',
            'notas'          => 'nullable|string',
        ]);

        try {
            return response()->json($this->service->procesar($data), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}