<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReporteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(private ReporteService $service) {}

    public function resumen(Request $request): JsonResponse
    {
        return response()->json($this->service->resumenGeneral($this->getFilters($request)));
    }

    public function ventasPorDia(Request $request): JsonResponse
    {
        return response()->json($this->service->ventasPorDia($this->getFilters($request)));
    }

    public function productosTop(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        $filters['limit'] = (int) $request->query('limit', 10);
        return response()->json($this->service->productosTopVendidos($filters));
    }

    public function ventasPorMetodo(Request $request): JsonResponse
    {
        return response()->json($this->service->ventasPorMetodo($this->getFilters($request)));
    }

    public function performanceCajeros(Request $request): JsonResponse
    {
        return response()->json($this->service->performanceCajeros($this->getFilters($request)));
    }

    public function stockCritico(Request $request): JsonResponse
    {
        return response()->json($this->service->stockCritico($this->getFilters($request)));
    }

    private function getFilters(Request $request): array
    {
        $filters = $request->only(['desde', 'hasta', 'sucursal_id']);
        if (isset($filters['sucursal_id'])) $filters['sucursal_id'] = (int) $filters['sucursal_id'];
        return $filters;
    }
}