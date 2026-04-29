<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Spatie\Permission\Models\Role;

class UserService
{
    public function __construct(private UserRepository $repository) {}

    public function getAll(array $filters = []): array
    {
        return $this->repository->all($filters)->map(fn($u) => $this->formatUser($u))->toArray();
    }

    public function getById(int $id): array
    {
        return $this->formatUser($this->repository->findOrFail($id));
    }

    public function create(array $data): array
    {
        $rol = $data['rol'] ?? null;
        unset($data['rol']);

        if ($rol && $rol !== 'super_admin' && empty($data['sucursal_id'])) {
            throw new \Exception('Los usuarios con este rol deben tener una sucursal asignada.');
        }

        if ($rol === 'super_admin') {
            $data['sucursal_id'] = null;
        }

        $user = $this->repository->create($data);

        if ($rol) {
            $user->assignRole($rol);
        }

        return $this->formatUser($user->fresh(['sucursal', 'roles']));
    }

    public function update(int $id, array $data): array
    {
        $rol = $data['rol'] ?? null;
        unset($data['rol']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($rol === 'super_admin') {
            $data['sucursal_id'] = null;
        }

        $user = $this->repository->findOrFail($id);
        $user = $this->repository->update($user, $data);

        if ($rol) {
            $user->syncRoles([$rol]);
        }

        return $this->formatUser($user->fresh(['sucursal', 'roles']));
    }

    public function delete(int $id): void
    {
        $user = $this->repository->findOrFail($id);

        if ($user->hasRole('super_admin')) {
            $totalSuperAdmins = User::role('super_admin')->count();
            if ($totalSuperAdmins <= 1) {
                throw new \Exception('No se puede eliminar al único super administrador del sistema.');
            }
        }

        $this->repository->delete($user);
    }

    public function getRolesDisponibles(): array
    {
        return Role::pluck('name')->toArray();
    }

    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'sucursal_id' => $user->sucursal_id,
            'sucursal'    => $user->sucursal,
            'activo'      => $user->activo,
            'roles'       => $user->getRoleNames()->toArray(),
            'created_at'  => $user->created_at,
            'updated_at'  => $user->updated_at,
        ];
    }
}