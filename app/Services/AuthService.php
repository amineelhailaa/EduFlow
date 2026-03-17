<?php

namespace App\Services;

use App\Contracts\AuthRepositoryInterface;

class AuthService
{
    public function __construct(
        private AuthRepositoryInterface $authRepository,
    ) {
    }

    public function register(array $attributes): array
    {
        $user = $this->authRepository->createUser($attributes);
        $token = $this->authRepository->createTokenForUser($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): ?array
    {
        $token = $this->authRepository->attempt($credentials);

        if ($token === null) {
            return null;
        }

        return [
            'user' => $this->authRepository->authenticatedUser(),
            'token' => $token,
        ];
    }
}
