<?php

declare(strict_types=1);

namespace Modules\Auth\Exceptions;

use Exception;

class AuthorizationException extends Exception
{
    public static function insufficientRole(): self
    {
        return new self('You do not have permission to perform this action.');
    }
}
