<?php

namespace App\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function createUser(array $attributes): User;

    public function createTokenForUser(User $user): string;

    public function attempt(array $credentials): ?string;

    public function authenticatedUser(): ?User;

    public function findUserByEmail(string $email): ?User;

    public function createResetPasswordToken(User $user): string;

    public function findUserByResetPasswordToken(string $token): ?User;

    public function updatePassword(User $user, string $password): void;

    public function invalidateToken(string $token): void;

    public function syncStudentInterests(User $user, array $interestIds): void;
}
