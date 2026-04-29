<?php

namespace App\Models;

use App\Scopes\SucursalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'sucursal_id',
        'mesa_id',
        'mozo_id',
        'numero',
        'tipo',
        'estado',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_direccion',
        'subtotal',
        'total',
        'notas',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'total'    => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SucursalScope());
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function mozo()
    {
        return $this->belongsTo(User::class, 'mozo_id');
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function recalcularTotales(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $this->update([
            'subtotal' => $subtotal,
            'total'    => $subtotal, // por ahora sin descuentos ni impuestos
        ]);
    }
}