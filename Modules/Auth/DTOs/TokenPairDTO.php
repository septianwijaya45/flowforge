<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

final readonly class TokenPairDTO
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int $expiresIn,
        public AuthenticatedUserDTO $user,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => 'bearer',
            'expires_in' => $this->expiresIn,
            'user' => $this->user->toArray(),
        ];
    }
}
