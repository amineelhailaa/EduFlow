<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {
    }

    #[OA\Post(
        path: '/api/v1/register',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Amine'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amine@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'role', type: 'string', enum: ['student', 'teacher'], example: 'student'),
                    new OA\Property(property: 'interest_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                ],
                example: [
                    'name' => 'Amine',
                    'email' => 'amine@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'role' => 'student',
                    'interest_ids' => [1, 2],
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'User registered successfully.',
                        'user' => [
                            'id' => 1,
                            'name' => 'Amine',
                            'email' => 'amine@example.com',
                            'role' => 'student',
                        ],
                        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'token_type' => 'bearer',
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'email' => ['The email has already been taken.'],
                        ],
                    ]
                )
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/login',
        summary: 'Login and receive JWT token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amine@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ],
                example: [
                    'email' => 'amine@example.com',
                    'password' => 'password123',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Login successful.',
                        'user' => [
                            'id' => 1,
                            'name' => 'Amine',
                            'email' => 'amine@example.com',
                            'role' => 'student',
                        ],
                        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'token_type' => 'bearer',
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Invalid credentials.',
                    ]
                )
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/forgot-password',
        summary: 'Generate reset password token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amine@example.com'),
                ],
                example: [
                    'email' => 'amine@example.com',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reset token generated',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'If the email exists, reset token was generated.',
                        'reset_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'expires_in_minutes' => 60,
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'email' => ['The email field must be a valid email address.'],
                        ],
                    ]
                )
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/reset-password',
        summary: 'Reset password using reset token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newpassword123'),
                ],
                example: [
                    'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successful',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Password reset successful.',
                        'user' => [
                            'id' => 1,
                            'name' => 'Amine',
                            'email' => 'amine@example.com',
                            'role' => 'student',
                        ],
                        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'token_type' => 'bearer',
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid token or validation error',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Invalid or expired reset token.',
                    ]
                )
            ),
        ]
    )]
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
