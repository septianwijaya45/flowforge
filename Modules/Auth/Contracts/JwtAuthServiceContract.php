<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts;

use Modules\Auth\DTOs\LoginCredentialsDTO;
use Modules\Auth\DTOs\TokenPairDTO;
use Modules\Auth\Models\User;

interface JwtAuthServiceContract
{
    public function login(LoginCredentialsDTO $credentials): TokenPairDTO;

    public function refresh(string $refreshToken): TokenPairDTO;

    public function logout(User $user, string $refreshToken): void;

    public function authenticateAccessToken(string $token): User;

    public function issueForUser(User $user): TokenPairDTO;
}
