<?php

namespace App\Models;

use App\Scopes\SucursalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaSesion extends Model
{
    use HasFactory;

    protected $table = 'caja_sesiones';

    protected $fillable = [
        'sucursal_id',
        'cajero_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_esperado',
        'monto_real',
        'diferencia',
        'estado',
        'notas_apertura',
        'notas_cierre',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre'   => 'datetime',
        'monto_inicial'  => 'float',
        'monto_esperado' => 'float',
        'monto_real'     => 'float',
        'diferencia'     => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SucursalScope());
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function cajero()
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'caja_sesion_id');
    }
}