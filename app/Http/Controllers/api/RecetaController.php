<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    public function __construct(private RecetaService $service) {}

    public function porProducto(int $productoId): JsonResponse
    {
        return response()->json($this->service->getByProducto($productoId));
    }

    public function sync(Request $request, int $productoId): JsonResponse
    {
        $data = $request->validate([
            'items'                => 'required|array',
            'items.*.item_id'      => 'required|exists:inventario_items,id',
            'items.*.cantidad'     => 'required|numeric|min:0.001',
            'items.*.notas'        => 'nullable|string',
        ]);

        return response()->json($this->service->sync($productoId, $data['items']));
    }
}