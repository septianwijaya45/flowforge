<?php

declare(strict_types=1);

namespace Modules\Retry\DTOs;

use Modules\Retry\Contracts\RetryStrategyContract;

final readonly class RetryConfigurationDTO
{
    public function __construct(
        public int $maxAttempts = 3,
        public ?RetryStrategyContract $strategy = null,
    ) {
        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('maxAttempts must be at least 1.');
        }
    }
}
