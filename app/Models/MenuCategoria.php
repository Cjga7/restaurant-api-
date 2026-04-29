<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCategoria extends Model
{
    use HasFactory;

    protected $table = 'menu_categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function productos()
    {
        return $this->hasMany(MenuProducto::class, 'categoria_id');
    }
}