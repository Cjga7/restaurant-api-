<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuProducto extends Model
{
     use SoftDeletes;
    use HasFactory;

    protected $table = 'menu_productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'imagen',
        'precio_base',
        'activo',
    ];

    protected $casts = [
        'precio_base' => 'float',
        'activo'      => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(MenuCategoria::class, 'categoria_id');
    }

    public function sucursales()
{
    return $this->belongsToMany(
            Sucursal::class,
            'menu_producto_sucursal',
            'producto_id',   // ← nombre real en la pivot
            'sucursal_id'
        )
        ->withPivot('precio', 'disponible')
        ->withTimestamps();
}

    // Precio efectivo para una sucursal dada
    public function precioParaSucursal(int $sucursalId): float
    {
        $pivot = $this->sucursales()
                      ->wherePivot('sucursal_id', $sucursalId)
                      ->first();

        return $pivot?->pivot->precio ?? $this->precio_base;
    }
    public function recetas()
{
    return $this->hasMany(Receta::class, 'producto_id');
}
}