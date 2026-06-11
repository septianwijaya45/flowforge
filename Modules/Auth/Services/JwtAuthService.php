<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\Contracts\JwtAuthServiceContract;
use Modules\Auth\Contracts\JwtTokenManagerContract;
use Modules\Auth\DTOs\AuthenticatedUserDTO;
use Modules\Auth\DTOs\LoginCredentialsDTO;
use Modules\Auth\DTOs\TokenPairDTO;
use Modules\Auth\Exceptions\AuthenticationException;
use Modules\Auth\Exceptions\InvalidTokenException;
use Modules\Auth\Models\RefreshToken;
use Modules\Auth\Models\User;

class JwtAuthService implements JwtAuthServiceContract
{
    public function __construct(
        private readonly JwtTokenManagerContract $tokenManager,
    ) {}

    public function login(LoginCredentialsDTO $credentials): TokenPairDTO
    {
        $user = User::query()
            ->where('email', $credentials->email)
            ->first();

        if ($user === null || ! Hash::check($credentials->password, $user->password)) {
            throw AuthenticationException::invalidCredentials();
        }

        return $this->issueForUser($user);
    }

    public function issueForUser(User $user): TokenPairDTO
    {
        return $this->issueTokenPair($user);
    }

    public function refresh(string $refreshToken): TokenPairDTO
    {
        $storedToken = RefreshToken::query()
            ->where('token_hash', $this->hashRefreshToken($refreshToken))
            ->where('expires_at', '>', now())
            ->first();

        if ($storedToken === null) {
            throw InvalidTokenException::refreshTokenInvalid();
        }

        $user = $storedToken->user;
        $storedToken->delete();

        return $this->issueTokenPair($user);
    }

    public function logout(User $user, string $refreshToken): void
    {
        RefreshToken::query()
            ->where('user_id', $user->id)
            ->where('token_hash', $this->hashRefreshToken($refreshToken))
            ->delete();
    }

    public function authenticateAccessToken(string $token): User
    {
        $claims = $this->tokenManager->validateAccessToken($token);

        $user = User::query()
            ->where('uuid', $claims->subject)
            ->first();

        if ($user === null) {
            throw InvalidTokenException::userNotFound();
        }

        return $user;
    }

    private function issueTokenPair(User $user): TokenPairDTO
    {
        $refreshToken = Str::random(64);

        RefreshToken::query()->create([
            'user_id' => $user->id,
            'token_hash' => $this->hashRefreshToken($refreshToken),
            'expires_at' => now()->addSeconds((int) config('jwt.refresh_token_ttl', 604800)),
        ]);

        return new TokenPairDTO(
            accessToken: $this->tokenManager->createAccessToken($user),
            refreshToken: $refreshToken,
            expiresIn: (int) config('jwt.access_token_ttl', 3600),
            user: AuthenticatedUserDTO::fromUser($user),
        );
    }

    private function hashRefreshToken(string $refreshToken): string
    {
        return hash('sha256', $refreshToken);
    }
}
