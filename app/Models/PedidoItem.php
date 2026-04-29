<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_items';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'producto_nombre',
        'precio_unitario',
        'cantidad',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'float',
        'subtotal'        => 'float',
        'cantidad'        => 'integer',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(MenuProducto::class, 'producto_id');
    }
}