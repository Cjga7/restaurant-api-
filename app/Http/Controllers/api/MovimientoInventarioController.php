<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MovimientoInventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovimientoInventarioController extends Controller
{
    public function __construct(private MovimientoInventarioService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['sucursal_id', 'item_id', 'tipo', 'desde', 'hasta', 'limit']);
        if (isset($filters['sucursal_id'])) $filters['sucursal_id'] = (int) $filters['sucursal_id'];
        if (isset($filters['item_id']))     $filters['item_id']     = (int) $filters['item_id'];
        if (isset($filters['limit']))       $filters['limit']       = (int) $filters['limit'];

        return response()->json($this->service->getAll($filters));
    }
}