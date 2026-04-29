<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;
    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'telefono',
        'email',
        'logo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}