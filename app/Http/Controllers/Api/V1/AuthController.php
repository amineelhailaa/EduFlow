<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

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
}
