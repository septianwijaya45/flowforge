<?php

declare(strict_types=1);

namespace Modules\Retry\DTOs;

final readonly class RetryDecisionDTO
{
    public function __construct(
        public bool $shouldRetry,
        public int $nextAttempt,
        public int $delaySeconds,
        public bool $exhausted,
    ) {}
}
