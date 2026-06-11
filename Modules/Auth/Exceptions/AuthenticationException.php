<?php

declare(strict_types=1);

namespace Modules\Auth\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    public static function invalidCredentials(): self
    {
        return new self('The provided credentials are incorrect.');
    }

    public static function unauthenticated(): self
    {
        return new self('Unauthenticated.');
    }
}
