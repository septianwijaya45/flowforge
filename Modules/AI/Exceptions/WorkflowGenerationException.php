<?php

declare(strict_types=1);

namespace Modules\AI\Exceptions;

use RuntimeException;
use Throwable;

final class WorkflowGenerationException extends RuntimeException
{
    public static function maxRetriesExceeded(int $attempts, Throwable $previous): self
    {
        return new self(
            "Failed to generate a valid workflow definition after {$attempts} attempts.",
            previous: $previous,
        );
    }
}
