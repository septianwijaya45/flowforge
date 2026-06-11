<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Contracts\JwtAuthServiceContract;
use Modules\Auth\DTOs\LoginCredentialsDTO;
use Modules\Auth\Exceptions\AuthenticationException;
use Modules\Auth\Exceptions\InvalidTokenException;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\LogoutRequest;
use Modules\Auth\Http\Requests\RefreshTokenRequest;
use Modules\Auth\Support\ApiResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly JwtAuthServiceContract $jwtAuthService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $tokens = $this->jwtAuthService->login(new LoginCredentialsDTO(
                email: $request->string('email')->toString(),
                password: $request->string('password')->toString(),
            ));
        } catch (AuthenticationException $exception) {
            return ApiResponse::error($exception->getMessage(), status: 401);
        }

        return ApiResponse::success($tokens->toArray(), 'Login successful');
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $tokens = $this->jwtAuthService->refresh(
                $request->string('refresh_token')->toString(),
            );
        } catch (InvalidTokenException $exception) {
            return ApiResponse::error($exception->getMessage(), status: 401);
        }

        return ApiResponse::success($tokens->toArray(), 'Token refreshed');
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error('Unauthenticated.', status: 401);
        }

        $this->jwtAuthService->logout(
            $user,
            $request->string('refresh_token')->toString(),
        );

        return ApiResponse::success(message: 'Logout successful');
    }
}
