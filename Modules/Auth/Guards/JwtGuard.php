<?php

declare(strict_types=1);

namespace Modules\Auth\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Modules\Auth\Contracts\JwtAuthServiceContract;
use Modules\Auth\Exceptions\InvalidTokenException;
class JwtGuard implements Guard
{
    private ?Authenticatable $user = null;

    private bool $userResolved = false;

    public function __construct(
        private readonly UserProvider $provider,
        private readonly Request $request,
        private readonly JwtAuthServiceContract $jwtAuthService,
    ) {}

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->userResolved) {
            return $this->user;
        }

        $this->userResolved = true;

        $token = $this->request->bearerToken();

        if ($token === null) {
            return null;
        }

        try {
            $this->user = $this->jwtAuthService->authenticateAccessToken($token);
        } catch (InvalidTokenException) {
            $this->user = null;
        }

        return $this->user;
    }

    public function id(): int|string|null
    {
        $user = $this->user();

        if ($user === null) {
            return null;
        }

        return $user->getAuthIdentifier();
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function validate(array $credentials = []): bool
    {
        if (! isset($credentials['email'], $credentials['password'])) {
            return false;
        }

        $user = $this->provider->retrieveByCredentials([
            'email' => $credentials['email'],
        ]);

        if ($user === null) {
            return false;
        }

        return $this->provider->validateCredentials($user, [
            'password' => $credentials['password'],
        ]);
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        $this->userResolved = true;

        return $this;
    }
}
