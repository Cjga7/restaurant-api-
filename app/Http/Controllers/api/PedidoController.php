<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function __construct(private PedidoService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['estado', 'tipo', 'sucursal_id', 'mesa_id', 'activos']);
        if (isset($filters['sucursal_id'])) $filters['sucursal_id'] = (int) $filters['sucursal_id'];
        if (isset($filters['mesa_id']))     $filters['mesa_id']     = (int) $filters['mesa_id'];
        if (isset($filters['activos']))     $filters['activos']     = filter_var($filters['activos'], FILTER_VALIDATE_BOOLEAN);

        return response()->json($this->service->getAll($filters));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sucursal_id'        => 'required|exists:sucursales,id',
            'mesa_id'            => 'nullable|exists:mesas,id',
            'tipo'               => 'required|in:mesa,delivery,llevar',
            'cliente_nombre'     => 'nullable|string|max:100',
            'cliente_telefono'   => 'nullable|string|max:20',
            'cliente_direccion'  => 'nullable|string|max:200',
            'notas'              => 'nullable|string',
            'items'              => 'nullable|array',
            'items.*.producto_id'=> 'required_with:items|exists:menu_productos,id',
            'items.*.cantidad'   => 'required_with:items|integer|min:1',
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'cliente_nombre'    => 'nullable|string|max:100',
            'cliente_telefono'  => 'nullable|string|max:20',
            'cliente_direccion' => 'nullable|string|max:200',
            'notas'             => 'nullable|string',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Pedido eliminado correctamente.']);
    }

    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'estado' => 'required|in:abierto,enviado,en_preparacion,listo,entregado,pagado,cancelado',
        ]);

        return response()->json($this->service->cambiarEstado($id, $data['estado']));
    }

    public function agregarItem(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'producto_id' => 'required|exists:menu_productos,id',
            'cantidad'    => 'integer|min:1',
        ]);

        return response()->json($this->service->agregarItem($id, $data));
    }

    public function actualizarItem(Request $request, int $id, int $itemId): JsonResponse
    {
        $data = $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        return response()->json($this->service->actualizarCantidadItem($id, $itemId, $data['cantidad']));
    }

    public function eliminarItem(int $id, int $itemId): JsonResponse
    {
        return response()->json($this->service->eliminarItem($id, $itemId));
    }
}