<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

use Exception;
use Throwable;

class WorkflowValidationException extends Exception
{
    /**
     * @param  array<string, string|list<string>>  $errors
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param  array<string, string|list<string>>  $errors
     */
    public static function withErrors(string $message, array $errors): self
    {
        return new self($message, $errors);
    }
}
