<?php

namespace App\Models;

use App\Scopes\SucursalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioStock extends Model
{
    use HasFactory;

    protected $table = 'inventario_stock';

    protected $fillable = [
        'sucursal_id',
        'item_id',
        'stock_actual',
        'stock_minimo',
        'stock_ideal',
        'precio_compra',
    ];

    protected $casts = [
        'stock_actual'  => 'float',
        'stock_minimo'  => 'float',
        'stock_ideal'   => 'float',
        'precio_compra' => 'float',
    ];

    protected $appends = ['alerta_bajo', 'valor_stock'];

    protected static function booted(): void
    {
        static::addGlobalScope(new SucursalScope());
    }

    public function item()
    {
        return $this->belongsTo(InventarioItem::class, 'item_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function getAlertaBajoAttribute(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function getValorStockAttribute(): float
    {
        return round($this->stock_actual * ($this->precio_compra ?? 0), 2);
    }
}