<?php

declare(strict_types=1);

use Modules\Retry\DTOs\RetryConfigurationDTO;
use Modules\Retry\Services\ExponentialBackoffStrategy;

describe('ExponentialBackoffStrategy', function (): void {
    it('calculates exponential delays for subsequent attempts', function (): void {
        $strategy = new ExponentialBackoffStrategy(
            baseDelaySeconds: 1,
            multiplier: 2.0,
            maxDelaySeconds: 300,
        );

        expect($strategy->delaySeconds(1))->toBe(0)
            ->and($strategy->delaySeconds(2))->toBe(1)
            ->and($strategy->delaySeconds(3))->toBe(2)
            ->and($strategy->delaySeconds(4))->toBe(4)
            ->and($strategy->delaySeconds(5))->toBe(8);
    });

    it('caps delay at the configured maximum', function (): void {
        $strategy = new ExponentialBackoffStrategy(
            baseDelaySeconds: 10,
            multiplier: 3.0,
            maxDelaySeconds: 50,
        );

        expect($strategy->delaySeconds(6))->toBe(50);
    });

    it('allows retries until max attempts are exhausted', function (): void {
        $strategy = new ExponentialBackoffStrategy;

        expect($strategy->shouldRetry(1, 3))->toBeTrue()
            ->and($strategy->shouldRetry(2, 3))->toBeTrue()
            ->and($strategy->shouldRetry(3, 3))->toBeFalse();
    });

    it('identifies itself as exponential_backoff', function (): void {
        expect((new ExponentialBackoffStrategy)->identifier())->toBe('exponential_backoff');
    });
});

describe('RetryStrategy configuration', function (): void {
    it('accepts configurable max attempts', function (): void {
        $configuration = new RetryConfigurationDTO(maxAttempts: 5);

        expect($configuration->maxAttempts)->toBe(5);
    });

    it('rejects invalid max attempt values', function (): void {
        expect(fn () => new RetryConfigurationDTO(maxAttempts: 0))
            ->toThrow(InvalidArgumentException::class);
    });
});
