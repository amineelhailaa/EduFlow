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
        $interestIds = $attributes['interest_ids'] ?? [];
        unset($attributes['interest_ids']);

        $user = $this->authRepository->createUser($attributes);

        if (($attributes['role'] ?? 'student') === 'student' && $interestIds !== []) {
            $this->authRepository->syncStudentInterests($user, $interestIds);
        }

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

    public function generateResetPasswordToken(string $email): ?string
    {
        $user = $this->authRepository->findUserByEmail($email);

        if (! $user) {
            return null;
        }

        return $this->authRepository->createResetPasswordToken($user);
    }

    public function resetPassword(string $resetToken, string $newPassword): ?array
    {
        $user = $this->authRepository->findUserByResetPasswordToken($resetToken);

        if (! $user) {
            return null;
        }

        $this->authRepository->updatePassword($user, $newPassword);
        $this->authRepository->invalidateToken($resetToken);

        return [
            'user' => $user,
            'token' => $this->authRepository->createTokenForUser($user),
        ];
    }
}
