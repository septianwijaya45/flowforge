<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts;

use Modules\Auth\DTOs\AccessTokenClaimsDTO;
use Modules\Auth\Models\User;

interface JwtTokenManagerContract
{
    public function createAccessToken(User $user): string;

    public function validateAccessToken(string $token): AccessTokenClaimsDTO;
}
