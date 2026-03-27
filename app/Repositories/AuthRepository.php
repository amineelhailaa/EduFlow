<?php

namespace App\Repositories;

use App\Contracts\AuthRepositoryInterface;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

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

    public function findUserByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function createResetPasswordToken(User $user): string
    {

        $payload = JWTFactory::customClaims([
            'sub' => 'reset-password',
            'uid' => $user->id,
            'email' => $user->email,
            'type' => 'reset_password',
            'exp' => now()->addMinutes(60)->timestamp,
        ])->make(true);

        return JWTAuth::encode($payload)->get();
    }

    public function findUserByResetPasswordToken(string $token): ?User
    {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();
        } catch (\Throwable) {
            return null;
        }

        if ($payload->get('type') !== 'reset_password') {
            return null;
        }

        $userId = $payload->get('uid');
        $email = $payload->get('email');
        $user = User::query()->find($userId);

        if (! $user || $user->email !== $email) {
            return null;
        }

        return $user;
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }

    public function invalidateToken(string $token): void
    {
            JWTAuth::setToken($token)->invalidate();

    }

    public function syncStudentInterests(User $user, array $interestIds): void
    {
        $user->studentInterests()->sync($interestIds);
    }
}
