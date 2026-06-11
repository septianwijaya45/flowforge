<?php

declare(strict_types=1);

namespace Modules\Auth\Exceptions;

use Exception;

class InvalidTokenException extends Exception
{
    public static function accessTokenInvalid(): self
    {
        return new self('The access token is invalid or has expired.');
    }

    public static function refreshTokenInvalid(): self
    {
        return new self('The refresh token is invalid or has expired.');
    }

    public static function userNotFound(): self
    {
        return new self('The token subject user was not found.');
    }
}
