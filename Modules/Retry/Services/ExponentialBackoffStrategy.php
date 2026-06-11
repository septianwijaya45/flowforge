<?php

declare(strict_types=1);

namespace Modules\Retry\Services;

use Modules\Retry\Contracts\RetryStrategyContract;

/**
 * Exponential backoff retry strategy.
 *
 * Delay for attempt N (N >= 2): min(base * multiplier^(N-2), maxDelay)
 */
class ExponentialBackoffStrategy implements RetryStrategyContract
{
    public function __construct(
        private readonly int $baseDelaySeconds = 1,
        private readonly float $multiplier = 2.0,
        private readonly int $maxDelaySeconds = 300,
    ) {
        if ($baseDelaySeconds < 0) {
            throw new \InvalidArgumentException('baseDelaySeconds must be zero or greater.');
        }

        if ($multiplier < 1.0) {
            throw new \InvalidArgumentException('multiplier must be at least 1.');
        }

        if ($maxDelaySeconds < $baseDelaySeconds) {
            throw new \InvalidArgumentException('maxDelaySeconds must be greater than or equal to baseDelaySeconds.');
        }
    }

    public function identifier(): string
    {
        return 'exponential_backoff';
    }

    public function shouldRetry(int $attempt, int $maxAttempts): bool
    {
        return $attempt < $maxAttempts;
    }

    public function delaySeconds(int $attempt): int
    {
        if ($attempt <= 1) {
            return 0;
        }

        $retryIndex = $attempt - 1;
        $delay = (int) round($this->baseDelaySeconds * ($this->multiplier ** ($retryIndex - 1)));

        return min($delay, $this->maxDelaySeconds);
    }
}
