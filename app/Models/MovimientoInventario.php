<?php

namespace App\Models;

use App\Scopes\SucursalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'sucursal_id',
        'item_id',
        'user_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'precio_unitario',
        'motivo',
        'notas',
    ];

    protected $casts = [
        'cantidad'        => 'float',
        'stock_anterior'  => 'float',
        'stock_nuevo'     => 'float',
        'precio_unitario' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SucursalScope());
    }

    public function item()
    {
        return $this->belongsTo(InventarioItem::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}