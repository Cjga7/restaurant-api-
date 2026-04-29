<?php

namespace App\Models;

use App\Scopes\SucursalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'sucursal_id',
        'numero',
        'capacidad',
        'estado',
        'qr_code',
        'ubicacion',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SucursalScope());
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}