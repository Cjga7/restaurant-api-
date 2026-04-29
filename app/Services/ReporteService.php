<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\InventarioStock;
use App\Models\CajaSesion;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    // ─── 1. Resumen general (KPIs) ────────────────
    public function resumenGeneral(array $filters): array
    {
        $desde      = $filters['desde'] ?? today()->toDateString();
        $hasta      = $filters['hasta'] ?? today()->toDateString();
        $sucursalId = $filters['sucursal_id'] ?? null;

        $pagos = Pago::query()
            ->when($sucursalId, fn($q, $v) => $q->whereHas('pedido', fn($pq) => $pq->where('sucursal_id', $v)))
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->get();

        $ventasTotales = $pagos->sum('monto_total');
        $cantidadVentas = $pagos->count();
        $ticketPromedio = $cantidadVentas > 0 ? $ventasTotales / $cantidadVentas : 0;

        $pedidos = Pedido::withoutGlobalScopes()
            ->when($sucursalId, fn($q, $v) => $q->where('sucursal_id', $v))
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->get();

        $pedidosPagados   = $pedidos->where('estado', 'pagado')->count();
        $pedidosCancelados = $pedidos->where('estado', 'cancelado')->count();
        $tasaCancelacion = $pedidos->count() > 0
            ? ($pedidosCancelados / $pedidos->count()) * 100
            : 0;

        return [
            'ventas_totales'      => round($ventasTotales, 2),
            'cantidad_ventas'     => $cantidadVentas,
            'ticket_promedio'     => round($ticketPromedio, 2),
            'pedidos_totales'     => $pedidos->count(),
            'pedidos_pagados'     => $pedidosPagados,
            'pedidos_cancelados'  => $pedidosCancelados,
            'tasa_cancelacion'    => round($tasaCancelacion, 1),
            'periodo'             => ['desde' => $desde, 'hasta' => $hasta],
        ];
    }

    // ─── 2. Ventas por día ───────────────────────
    public function ventasPorDia(array $filters): array
    {
        $desde      = $filters['desde'] ?? today()->subDays(7)->toDateString();
        $hasta      = $filters['hasta'] ?? today()->toDateString();
        $sucursalId = $filters['sucursal_id'] ?? null;

        $rows = Pago::query()
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as total')
            )
            ->when($sucursalId, fn($q, $v) => $q->whereHas('pedido', fn($pq) => $pq->where('sucursal_id', $v)))
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn($r) => [
                'fecha'    => $r->fecha,
                'cantidad' => (int) $r->cantidad,
                'total'    => (float) $r->total,
            ])
            ->toArray();

        return $rows;
    }

    // ─── 3. Productos más vendidos ───────────────
    public function productosTopVendidos(array $filters): array
    {
        $desde      = $filters['desde'] ?? today()->subDays(30)->toDateString();
        $hasta      = $filters['hasta'] ?? today()->toDateString();
        $sucursalId = $filters['sucursal_id'] ?? null;
        $limit      = $filters['limit'] ?? 10;

        $rows = PedidoItem::query()
            ->select(
                'producto_id',
                'producto_nombre',
                DB::raw('SUM(cantidad) as cantidad_vendida'),
                DB::raw('SUM(subtotal) as total_recaudado')
            )
            ->whereHas('pedido', function($q) use ($sucursalId, $desde, $hasta) {
                $q->where('estado', 'pagado')
                  ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);
                if ($sucursalId) $q->where('sucursal_id', $sucursalId);
            })
            ->groupBy('producto_id', 'producto_nombre')
            ->orderByDesc('cantidad_vendida')
            ->limit($limit)
            ->get()
            ->map(fn($r) => [
                'producto_id'      => $r->producto_id,
                'producto_nombre'  => $r->producto_nombre,
                'cantidad_vendida' => (int) $r->cantidad_vendida,
                'total_recaudado'  => (float) $r->total_recaudado,
            ])
            ->toArray();

        return $rows;
    }

    // ─── 4. Distribución por método de pago ──────
    public function ventasPorMetodo(array $filters): array
    {
        $desde      = $filters['desde'] ?? today()->subDays(30)->toDateString();
        $hasta      = $filters['hasta'] ?? today()->toDateString();
        $sucursalId = $filters['sucursal_id'] ?? null;

        $rows = Pago::query()
            ->select(
                'metodo',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as total')
            )
            ->when($sucursalId, fn($q, $v) => $q->whereHas('pedido', fn($pq) => $pq->where('sucursal_id', $v)))
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->groupBy('metodo')
            ->get()
            ->map(fn($r) => [
                'metodo'   => $r->metodo,
                'cantidad' => (int) $r->cantidad,
                'total'    => (float) $r->total,
            ])
            ->toArray();

        return $rows;
    }

    // ─── 5. Performance por cajero ───────────────
    public function performanceCajeros(array $filters): array
    {
        $desde      = $filters['desde'] ?? today()->subDays(30)->toDateString();
        $hasta      = $filters['hasta'] ?? today()->toDateString();
        $sucursalId = $filters['sucursal_id'] ?? null;

        $rows = Pago::query()
            ->select(
                'cajero_id',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as total')
            )
            ->with('cajero:id,name')
            ->when($sucursalId, fn($q, $v) => $q->whereHas('pedido', fn($pq) => $pq->where('sucursal_id', $v)))
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->whereNotNull('cajero_id')
            ->groupBy('cajero_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'cajero_id'    => $r->cajero_id,
                'cajero_nombre' => $r->cajero?->name ?? 'Sin asignar',
                'cantidad'     => (int) $r->cantidad,
                'total'        => (float) $r->total,
            ])
            ->toArray();

        return $rows;
    }

    // ─── 6. Stock crítico (alertas) ──────────────
    public function stockCritico(array $filters): array
    {
        $sucursalId = $filters['sucursal_id'] ?? null;

        $rows = InventarioStock::with(['item', 'sucursal'])
            ->withoutGlobalScopes()
            ->when($sucursalId, fn($q, $v) => $q->where('sucursal_id', $v))
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->get()
            ->map(fn($s) => [
                'item_nombre'    => $s->item?->nombre,
                'categoria'      => $s->item?->categoria,
                'unidad'         => $s->item?->unidad,
                'sucursal'       => $s->sucursal?->nombre,
                'stock_actual'   => (float) $s->stock_actual,
                'stock_minimo'   => (float) $s->stock_minimo,
                'stock_ideal'    => (float) ($s->stock_ideal ?? 0),
            ])
            ->toArray();

        return $rows;
    }
}