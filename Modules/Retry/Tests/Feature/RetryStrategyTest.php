<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Retry\Contracts\RetryStrategyContract;
use Modules\Retry\DTOs\RetryConfigurationDTO;
use Modules\Retry\Enums\RetryHistoryStatus;
use Modules\Retry\Models\RetryHistory;
use Modules\Retry\Services\ExponentialBackoffStrategy;
use Modules\Retry\Services\RetryStrategy;
use Modules\Tenant\Models\Tenant;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

uses(RefreshDatabase::class);

describe('RetryStrategy', function (): void {
    it('is bound in the service container', function (): void {
        expect(app(RetryStrategyContract::class))->toBeInstanceOf(ExponentialBackoffStrategy::class)
            ->and(app(RetryStrategy::class))->toBeInstanceOf(RetryStrategy::class);
    });

    it('decides whether another retry should run with exponential backoff', function (): void {
        $retryStrategy = app(RetryStrategy::class);

        $decision = $retryStrategy->decide(1, new RetryConfigurationDTO(maxAttempts: 3));

        expect($decision->shouldRetry)->toBeTrue()
            ->and($decision->nextAttempt)->toBe(2)
            ->and($decision->delaySeconds)->toBe(1)
            ->and($decision->exhausted)->toBeFalse();

        $exhausted = $retryStrategy->decide(3, new RetryConfigurationDTO(maxAttempts: 3));

        expect($exhausted->shouldRetry)->toBeFalse()
            ->and($exhausted->exhausted)->toBeTrue()
            ->and($exhausted->delaySeconds)->toBe(0);
    });

    it('stores retry history for a retryable model', function (): void {
        $tenant = Tenant::query()->create([
            'name' => 'Retry Tenant',
            'slug' => 'retry-tenant-'.uniqid(),
            'is_active' => true,
        ]);

        $retryableType = WorkflowRunStep::class;
        $retryableId = '44444444-4444-4444-4444-444444444444';

        $retryStrategy = app(RetryStrategy::class);
        $configuration = new RetryConfigurationDTO(maxAttempts: 3);

        $history = $retryStrategy->recordScheduledRetry(
            retryableType: $retryableType,
            retryableId: $retryableId,
            attempt: 1,
            configuration: $configuration,
            delaySeconds: 2,
            tenantId: $tenant->id,
            error: ['message' => 'Temporary failure'],
            metadata: ['node_id' => 'http-step'],
        );

        expect($history)->toBeInstanceOf(RetryHistory::class)
            ->and($history->status)->toBe(RetryHistoryStatus::Scheduled)
            ->and($history->attempt)->toBe(1)
            ->and($history->max_attempts)->toBe(3)
            ->and($history->strategy)->toBe('exponential_backoff')
            ->and($history->delay_seconds)->toBe(2)
            ->and($history->tenant_id)->toBe($tenant->id);

        $records = $retryStrategy->history($retryableType, $retryableId);

        expect($records)->toHaveCount(1)
            ->and($records->first()?->error)->toBe(['message' => 'Temporary failure']);
    });

    it('tracks retry lifecycle status transitions', function (): void {
        $retryStrategy = app(RetryStrategy::class);

        $history = $retryStrategy->recordScheduledRetry(
            retryableType: 'workflow_run_step',
            retryableId: '55555555-5555-5555-5555-555555555555',
            attempt: 2,
            configuration: new RetryConfigurationDTO(maxAttempts: 4),
            delaySeconds: 4,
        );

        $retryStrategy->markCompleted($history);

        expect($history->fresh()?->status)->toBe(RetryHistoryStatus::Completed)
            ->and($history->fresh()?->attempted_at)->not->toBeNull();
    });

    it('marks retries as exhausted when max attempts are reached', function (): void {
        $retryStrategy = app(RetryStrategy::class);

        $history = $retryStrategy->recordScheduledRetry(
            retryableType: 'workflow_run_step',
            retryableId: '66666666-6666-6666-6666-666666666666',
            attempt: 3,
            configuration: new RetryConfigurationDTO(maxAttempts: 3),
            delaySeconds: 0,
        );

        $retryStrategy->markExhausted($history);

        expect($history->fresh()?->status)->toBe(RetryHistoryStatus::Exhausted);
    });

    it('supports custom backoff strategies per configuration', function (): void {
        $customStrategy = new ExponentialBackoffStrategy(
            baseDelaySeconds: 5,
            multiplier: 2.0,
            maxDelaySeconds: 100,
        );

        $decision = app(RetryStrategy::class)->decide(
            1,
            new RetryConfigurationDTO(maxAttempts: 5, strategy: $customStrategy),
        );

        expect($decision->delaySeconds)->toBe(5);
    });
});
