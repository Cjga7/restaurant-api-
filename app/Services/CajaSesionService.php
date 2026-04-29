<?php

namespace App\Services;

use App\Models\CajaSesion;
use App\Repositories\CajaSesionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CajaSesionService
{
    public function __construct(private CajaSesionRepository $repository) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function getById(int $id): CajaSesion
    {
        return $this->repository->findOrFail($id);
    }

    public function miSesionAbierta(int $sucursalId): ?CajaSesion
    {
        $sesion = $this->repository->sesionAbierta(Auth::id(), $sucursalId);
        return $sesion ? $sesion->load(['pagos.pedido']) : null;
    }

    public function abrir(array $data): CajaSesion
    {
        // Validar que el cajero no tenga otra sesión abierta en la misma sucursal
        $existente = $this->repository->sesionAbierta(Auth::id(), $data['sucursal_id']);
        if ($existente) {
            throw new \Exception('Ya tenés una sesión de caja abierta en esta sucursal. Cerrala antes de abrir una nueva.');
        }

        return $this->repository->create([
            'sucursal_id'    => $data['sucursal_id'],
            'cajero_id'      => Auth::id(),
            'fecha_apertura' => now(),
            'monto_inicial'  => $data['monto_inicial'],
            'notas_apertura' => $data['notas_apertura'] ?? null,
            'estado'         => 'abierta',
        ]);
    }

    public function cerrar(int $id, array $data): CajaSesion
    {
        $sesion = $this->repository->findOrFail($id);

        if ($sesion->estado === 'cerrada') {
            throw new \Exception('Esta sesión de caja ya está cerrada.');
        }

        // Calcular monto esperado: inicial + efectivo recaudado
        $efectivoRecaudado = $sesion->pagos->sum('monto_efectivo');
        $montoEsperado     = $sesion->monto_inicial + $efectivoRecaudado;
        $montoReal         = (float) $data['monto_real'];
        $diferencia        = $montoReal - $montoEsperado;

        return $this->repository->update($sesion, [
            'fecha_cierre'   => now(),
            'monto_esperado' => $montoEsperado,
            'monto_real'     => $montoReal,
            'diferencia'     => $diferencia,
            'notas_cierre'   => $data['notas_cierre'] ?? null,
            'estado'         => 'cerrada',
        ]);
    }
    public function getSesionesActivas(?int $sucursalId = null): \Illuminate\Database\Eloquent\Collection
{
    return $this->repository->sesionesActivas($sucursalId);
}
}