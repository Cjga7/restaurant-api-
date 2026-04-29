<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioItem extends Model
{
    use HasFactory;

    protected $table = 'inventario_items';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'unidad',
        'producto_menu_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(InventarioStock::class, 'item_id');
    }

    public function stockEnSucursal(int $sucursalId): ?InventarioStock
    {
        return $this->stocks()->where('sucursal_id', $sucursalId)->first();
    }

    public function productoMenu()
    {
        return $this->belongsTo(MenuProducto::class, 'producto_menu_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'item_id');
    }
}