<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::with('sucursal')->where('email', $email)->first();
    }

    public function createToken(User $user): string
    {
        return $user->createToken('api-token')->plainTextToken;
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}