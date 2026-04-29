<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventarioStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioStockController extends Controller
{
    public function __construct(private InventarioStockService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['sucursal_id', 'categoria', 'alerta']);
        if (isset($filters['sucursal_id'])) $filters['sucursal_id'] = (int) $filters['sucursal_id'];
        if (isset($filters['alerta']))      $filters['alerta']      = filter_var($filters['alerta'], FILTER_VALIDATE_BOOLEAN);

        return response()->json($this->service->getAll($filters));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function updateUmbrales(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'stock_minimo'  => 'nullable|numeric|min:0',
            'stock_ideal'   => 'nullable|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
        ]);

        return response()->json($this->service->actualizarUmbrales($id, $data));
    }

    public function movimiento(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id'     => 'required|exists:sucursales,id',
            'item_id'         => 'required|exists:inventario_items,id',
            'tipo'            => 'required|in:entrada,salida,ajuste',
            'cantidad'        => 'required|numeric|min:0',
            'precio_unitario' => 'nullable|numeric|min:0',
            'motivo'          => 'nullable|in:compra,consumo,merma,descarte,robo,devolucion,transferencia,inventario_fisico,otro',
            'notas'           => 'nullable|string',
        ]);

        return response()->json($this->service->registrarMovimiento($data), 201);
    }
    public function transferir(Request $request): JsonResponse
{
    $data = $request->validate([
        'sucursal_origen_id'  => 'required|exists:sucursales,id|different:sucursal_destino_id',
        'sucursal_destino_id' => 'required|exists:sucursales,id',
        'item_id'             => 'required|exists:inventario_items,id',
        'cantidad'            => 'required|numeric|min:0.001',
        'notas'               => 'nullable|string',
    ]);

    return response()->json($this->service->transferirEntreSucursales($data));
}
}