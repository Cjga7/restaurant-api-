<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'pedido_id',
        'caja_sesion_id',
        'cajero_id',
        'metodo',
        'monto_total',
        'monto_efectivo',
        'monto_tarjeta',
        'monto_qr',
        'monto_recibido',
        'cambio',
        'referencia',
        'notas',
    ];

    protected $casts = [
        'monto_total'    => 'float',
        'monto_efectivo' => 'float',
        'monto_tarjeta'  => 'float',
        'monto_qr'       => 'float',
        'monto_recibido' => 'float',
        'cambio'         => 'float',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function sesion()
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    public function cajero()
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }
}