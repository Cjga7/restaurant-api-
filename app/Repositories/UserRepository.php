<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function all(array $filters = []): Collection
    {
        return User::with(['sucursal', 'roles'])
            ->when($filters['sucursal_id'] ?? null, fn($q, $v) => $q->where('sucursal_id', $v))
            ->when($filters['rol'] ?? null, fn($q, $v) => $q->whereHas('roles', fn($rq) => $rq->where('name', $v)))
            ->when(isset($filters['activo']), fn($q) => $q->where('activo', $filters['activo']))
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(int $id): User
    {
        return User::with(['sucursal', 'roles'])->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh(['sucursal', 'roles']);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}