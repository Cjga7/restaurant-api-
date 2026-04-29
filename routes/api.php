<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SucursalController;
use App\Http\Controllers\Api\MenuCategoriaController;
use App\Http\Controllers\Api\MenuProductoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MesaController;

use App\Http\Controllers\Api\EmpleadoController;

use App\Http\Controllers\Api\ReservaController;

use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\InventarioItemController;
use App\Http\Controllers\Api\InventarioStockController;
use App\Http\Controllers\Api\MovimientoInventarioController;
use App\Http\Controllers\Api\RecetaController;

use App\Http\Controllers\Api\CajaSesionController;
use App\Http\Controllers\Api\PagoController;

use App\Http\Controllers\Api\UserController;


use App\Http\Controllers\Api\ReporteController;



Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {

    // Sucursales
    Route::middleware('permission:sucursales.ver')->group(function () {
        Route::get('sucursales', [SucursalController::class, 'index']);
        Route::get('sucursales/{id}', [SucursalController::class, 'show']);
    });
    Route::middleware('permission:sucursales.crear')->post('sucursales', [SucursalController::class, 'store']);
    Route::middleware('permission:sucursales.editar')->put('sucursales/{id}', [SucursalController::class, 'update']);
    Route::middleware('permission:sucursales.eliminar')->delete('sucursales/{id}', [SucursalController::class, 'destroy']);

    // Menú — Categorías
    Route::middleware('permission:menu.ver')->group(function () {
        Route::get('menu/categorias', [MenuCategoriaController::class, 'index']);
        Route::get('menu/categorias/{id}', [MenuCategoriaController::class, 'show']);
        Route::get('menu/productos', [MenuProductoController::class, 'index']);
        Route::get('menu/productos/{id}', [MenuProductoController::class, 'show']);
        Route::get('menu/sucursal/{sucursalId}/productos', [MenuProductoController::class, 'paraSucursal']);
    });
    Route::middleware('permission:menu.gestionar')->group(function () {
        Route::post('menu/categorias', [MenuCategoriaController::class, 'store']);
        Route::put('menu/categorias/{id}', [MenuCategoriaController::class, 'update']);
        Route::delete('menu/categorias/{id}', [MenuCategoriaController::class, 'destroy']);
        Route::post('menu/productos', [MenuProductoController::class, 'store']);
        Route::put('menu/productos/{id}', [MenuProductoController::class, 'update']);
        Route::delete('menu/productos/{id}', [MenuProductoController::class, 'destroy']);
        Route::post('menu/productos/{id}/sucursal', [MenuProductoController::class, 'configurarSucursal']);
    });
    // Mesas
Route::middleware('permission:mesas.ver')->group(function () {
    Route::get('mesas', [MesaController::class, 'index']);
    Route::get('mesas/{id}', [MesaController::class, 'show']);
});
Route::middleware('permission:mesas.gestionar')->group(function () {
    Route::post('mesas', [MesaController::class, 'store']);
    Route::put('mesas/{id}', [MesaController::class, 'update']);
    Route::delete('mesas/{id}', [MesaController::class, 'destroy']);
    Route::patch('mesas/{id}/estado', [MesaController::class, 'cambiarEstado']);
});
// Empleados
Route::middleware('permission:empleados.ver')->group(function () {
    Route::get('empleados', [EmpleadoController::class, 'index']);
    Route::get('empleados/{id}', [EmpleadoController::class, 'show']);
});
Route::middleware('permission:empleados.gestionar')->group(function () {
    Route::post('empleados', [EmpleadoController::class, 'store']);
    Route::put('empleados/{id}', [EmpleadoController::class, 'update']);
    Route::delete('empleados/{id}', [EmpleadoController::class, 'destroy']);
});
// Reservas
Route::middleware('permission:reservas.ver')->group(function () {
    Route::get('reservas', [ReservaController::class, 'index']);
    Route::get('reservas/{id}', [ReservaController::class, 'show']);
});
Route::middleware('permission:reservas.gestionar')->group(function () {
    Route::post('reservas', [ReservaController::class, 'store']);
    Route::put('reservas/{id}', [ReservaController::class, 'update']);
    Route::delete('reservas/{id}', [ReservaController::class, 'destroy']);
    Route::patch('reservas/{id}/estado', [ReservaController::class, 'cambiarEstado']);
    Route::post('reservas/{id}/cliente-llego', [ReservaController::class, 'clienteLlego']);
});
// Pedidos
Route::middleware('permission:pedidos.ver')->group(function () {
    Route::get('pedidos', [PedidoController::class, 'index']);
    Route::get('pedidos/{id}', [PedidoController::class, 'show']);
});
Route::middleware('permission:pedidos.crear')->post('pedidos', [PedidoController::class, 'store']);
Route::middleware('permission:pedidos.gestionar')->group(function () {
    Route::put('pedidos/{id}', [PedidoController::class, 'update']);
    Route::delete('pedidos/{id}', [PedidoController::class, 'destroy']);
    Route::patch('pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado']);

    // Gestión de items del pedido
    Route::post('pedidos/{id}/items', [PedidoController::class, 'agregarItem']);
    Route::put('pedidos/{id}/items/{itemId}', [PedidoController::class, 'actualizarItem']);
    Route::delete('pedidos/{id}/items/{itemId}', [PedidoController::class, 'eliminarItem']);
});
// Inventario
Route::middleware('permission:inventario.ver')->group(function () {
    Route::get('inventario/items',        [InventarioItemController::class, 'index']);
    Route::get('inventario/items/{id}',   [InventarioItemController::class, 'show']);
    Route::get('inventario/stock',        [InventarioStockController::class, 'index']);
    Route::get('inventario/stock/{id}',   [InventarioStockController::class, 'show']);
    Route::get('inventario/movimientos',  [MovimientoInventarioController::class, 'index']);
    Route::post('inventario/transferencias', [InventarioStockController::class, 'transferir']);
});

Route::middleware('permission:inventario.gestionar')->group(function () {
    // Items globales
    Route::post('inventario/items',         [InventarioItemController::class, 'store']);
    Route::put('inventario/items/{id}',     [InventarioItemController::class, 'update']);
    Route::delete('inventario/items/{id}',  [InventarioItemController::class, 'destroy']);

    // Stock por sucursal
    Route::put('inventario/stock/{id}/umbrales', [InventarioStockController::class, 'updateUmbrales']);
    Route::post('inventario/movimientos',        [InventarioStockController::class, 'movimiento']);
});

// Recetas
Route::middleware('permission:menu.ver')->group(function () {
    Route::get('productos/{productoId}/recetas', [RecetaController::class, 'porProducto']);
});
Route::middleware('permission:menu.gestionar')->group(function () {
    Route::post('productos/{productoId}/recetas', [RecetaController::class, 'sync']);
});
// Caja
Route::middleware('permission:caja.ver')->group(function () {
    Route::get('caja/sesiones',         [CajaSesionController::class, 'index']);
    Route::get('caja/sesiones/{id}',    [CajaSesionController::class, 'show']);
    Route::get('caja/mi-sesion',        [CajaSesionController::class, 'miSesion']);
    Route::get('pagos',                 [PagoController::class, 'index']);
    Route::get('caja/sesiones-activas', [CajaSesionController::class, 'activas']);
});
Route::middleware('permission:caja.gestionar')->group(function () {
    Route::post('caja/sesiones',          [CajaSesionController::class, 'abrir']);
    Route::patch('caja/sesiones/{id}/cerrar', [CajaSesionController::class, 'cerrar']);
    Route::post('pagos',                  [PagoController::class, 'store']);
});
// Usuarios
Route::middleware('permission:usuarios.ver')->group(function () {
    Route::get('users',          [UserController::class, 'index']);
    Route::get('users/{id}',     [UserController::class, 'show']);
    Route::get('roles',          [UserController::class, 'roles']);
});
Route::middleware('permission:usuarios.gestionar')->group(function () {
    Route::post('users',          [UserController::class, 'store']);
    Route::put('users/{id}',      [UserController::class, 'update']);
    Route::delete('users/{id}',   [UserController::class, 'destroy']);
});
// Reportes
Route::middleware('permission:reportes.ver')->prefix('reportes')->group(function () {
    Route::get('resumen',        [ReporteController::class, 'resumen']);
    Route::get('ventas-por-dia', [ReporteController::class, 'ventasPorDia']);
    Route::get('productos-top',  [ReporteController::class, 'productosTop']);
    Route::get('ventas-metodo',  [ReporteController::class, 'ventasPorMetodo']);
    Route::get('cajeros',        [ReporteController::class, 'performanceCajeros']);
    Route::get('stock-critico',  [ReporteController::class, 'stockCritico']);
});


});