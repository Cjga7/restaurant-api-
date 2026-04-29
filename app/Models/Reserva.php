<?php

namespace App\Models;

use App\Scopes\SucursalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'sucursal_id',
        'mesa_id',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'cantidad_personas',
        'fecha_hora',
        'estado',
        'notas',
        'registrada_por',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
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

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }
}