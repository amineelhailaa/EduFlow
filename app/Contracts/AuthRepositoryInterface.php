<?php

namespace App\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function createUser(array $attributes): User;

    public function createTokenForUser(User $user): string;

    public function attempt(array $credentials): ?string;

    public function authenticatedUser(): ?User;
}
