<?php

declare(strict_types=1);

namespace Modules\Retry\Contracts;

interface RetryStrategyContract
{
    public function identifier(): string;

    public function shouldRetry(int $attempt, int $maxAttempts): bool;

    public function delaySeconds(int $attempt): int;
}
