<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private AuthRepository $repository) {}

    public function login(array $data): array
    {
        $user = $this->repository->findByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new \Exception('Credenciales incorrectas.', 401);
        }

        $token = $this->repository->createToken($user);

        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $this->repository->revokeAllTokens($user);
    }

    public function me(User $user): array
    {
        $user->load('sucursal');
        return $this->formatUser($user);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'sucursal_id' => $user->sucursal_id,
            'sucursal'    => $user->sucursal,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }
}