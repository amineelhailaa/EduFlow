<?php

namespace App\Repositories;

use App\Contracts\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    public function createUser(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function createTokenForUser(User $user): string
    {
        return auth('api')->login($user);
    }

    public function attempt(array $credentials): ?string
    {
        $token = auth('api')->attempt($credentials);

        return $token ?: null;
    }

    public function authenticatedUser(): ?User
    {
        return auth('api')->user();
    }
}
