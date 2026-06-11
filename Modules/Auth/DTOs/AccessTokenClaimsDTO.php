<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use Modules\Auth\Enums\UserRole;

final readonly class AccessTokenClaimsDTO
{
    public function __construct(
        public string $subject,
        public UserRole $role,
        public string $jti,
        public int $issuedAt,
        public int $expiresAt,
    ) {}
}
