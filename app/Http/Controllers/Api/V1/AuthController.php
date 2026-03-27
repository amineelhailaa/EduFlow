<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $authData = $this->authService->register($request->safe()->only([
            'name',
            'email',
            'role',
            'password',
            'interest_ids',
        ]));

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $authData['user'],
            'token' => $authData['token'],
            'token_type' => 'bearer',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $authData = $this->authService->login($request->safe()->only([
            'email',
            'password',
        ]));

        if ($authData === null) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful.',
            'user' => $authData['user'],
            'token' => $authData['token'],
            'token_type' => 'bearer',
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $resetToken = $this->authService->generateResetPasswordToken($data['email']);

        return response()->json([
            'message' => 'If the email exists, reset token was generated.',
            'reset_token' => $resetToken,
            'expires_in_minutes' => (int) config('auth.passwords.users.expire', 60),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $authData = $this->authService->resetPassword($data['token'], $data['password']);

        if (! $authData) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 422);
        }

        return response()->json([
            'message' => 'Password reset successful.',
            'user' => $authData['user'],
            'token' => $authData['token'],
            'token_type' => 'bearer',
        ]);
    }
}
