<?php

namespace App\Models;

use App\Scopes\SucursalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'sucursal_id',
        'user_id',
        'nombres',
        'apellidos',
        'ci',
        'telefono',
        'email',
        'direccion',
        'fecha_nacimiento',
        'fecha_ingreso',
        'cargo',
        'turno',
        'salario',
        'foto',
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso'    => 'date',
        'salario'          => 'float',
        'activo'           => 'boolean',
    ];

    protected $appends = ['nombre_completo'];

    protected static function booted(): void
    {
        static::addGlobalScope(new SucursalScope());
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}