<?php

declare(strict_types=1);

use Modules\Retry\Contracts\RetryStrategyContract;
use Modules\Retry\DTOs\RetryConfigurationDTO;
use Modules\Retry\Services\ExponentialBackoffStrategy;
use Modules\Retry\Services\RetryStrategy;

/**
 * @param  bool  $shouldRetry
 */
function fixedRetryStrategy(
    bool $shouldRetry,
    int $delaySeconds = 0,
    string $identifier = 'fixed',
): RetryStrategyContract {
    return new class($shouldRetry, $delaySeconds, $identifier) implements RetryStrategyContract
    {
        public function __construct(
            private readonly bool $shouldRetry,
            private readonly int $delaySeconds,
            private readonly string $identifier,
        ) {}

        public function identifier(): string
        {
            return $this->identifier;
        }

        public function shouldRetry(int $attempt, int $maxAttempts): bool
        {
            return $this->shouldRetry;
        }

        public function delaySeconds(int $attempt): int
        {
            return $this->delaySeconds;
        }
    };
}

function retryStrategyWithBackoff(): RetryStrategy
{
    return new RetryStrategy(new ExponentialBackoffStrategy);
}

describe('RetryStrategy', function (): void {
    it('schedules another attempt when retries remain', function (): void {
        $decision = retryStrategyWithBackoff()->decide(1, new RetryConfigurationDTO(maxAttempts: 3));

        expect($decision->shouldRetry)->toBeTrue()
            ->and($decision->nextAttempt)->toBe(2)
            ->and($decision->delaySeconds)->toBe(1)
            ->and($decision->exhausted)->toBeFalse();
    });

    it('marks the final attempt as exhausted', function (): void {
        $decision = retryStrategyWithBackoff()->decide(3, new RetryConfigurationDTO(maxAttempts: 3));

        expect($decision->shouldRetry)->toBeFalse()
            ->and($decision->nextAttempt)->toBe(4)
            ->and($decision->delaySeconds)->toBe(0)
            ->and($decision->exhausted)->toBeTrue();
    });

    it('uses exponential backoff delays for intermediate attempts', function (): void {
        $retryStrategy = retryStrategyWithBackoff();
        $configuration = new RetryConfigurationDTO(maxAttempts: 5);

        expect($retryStrategy->decide(2, $configuration)->delaySeconds)->toBe(2)
            ->and($retryStrategy->decide(3, $configuration)->delaySeconds)->toBe(4);
    });

    it('delegates retry decisions to a configuration-specific strategy', function (): void {
        $customStrategy = fixedRetryStrategy(
            shouldRetry: true,
            delaySeconds: 42,
            identifier: 'custom',
        );

        $decision = retryStrategyWithBackoff()->decide(
            1,
            new RetryConfigurationDTO(maxAttempts: 3, strategy: $customStrategy),
        );

        expect($decision->shouldRetry)->toBeTrue()
            ->and($decision->delaySeconds)->toBe(42);
    });

    it('honours a configuration strategy that refuses further retries', function (): void {
        $customStrategy = fixedRetryStrategy(shouldRetry: false, delaySeconds: 99);

        $decision = retryStrategyWithBackoff()->decide(
            1,
            new RetryConfigurationDTO(maxAttempts: 5, strategy: $customStrategy),
        );

        expect($decision->shouldRetry)->toBeFalse()
            ->and($decision->exhausted)->toBeTrue()
            ->and($decision->delaySeconds)->toBe(0);
    });

    it('allows a single attempt when max attempts is one', function (): void {
        $decision = retryStrategyWithBackoff()->decide(1, new RetryConfigurationDTO(maxAttempts: 1));

        expect($decision->shouldRetry)->toBeFalse()
            ->and($decision->exhausted)->toBeTrue();
    });
});
