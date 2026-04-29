<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;

    protected $table = 'recetas';

    protected $fillable = [
        'producto_id',
        'item_id',
        'cantidad',
        'notas',
    ];

    protected $casts = [
        'cantidad' => 'float',
    ];

    public function producto()
    {
        return $this->belongsTo(MenuProducto::class, 'producto_id');
    }

    public function item()
    {
        return $this->belongsTo(InventarioItem::class, 'item_id');
    }
}