<?php

declare(strict_types=1);

namespace Modules\Retry\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Modules\Retry\Contracts\RetryStrategyContract;
use Modules\Retry\DTOs\RecordRetryAttemptDTO;
use Modules\Retry\DTOs\RetryConfigurationDTO;
use Modules\Retry\DTOs\RetryDecisionDTO;
use Modules\Retry\Enums\RetryHistoryStatus;
use Modules\Retry\Models\RetryHistory;

/**
 * Coordinates retry decisions, backoff delays, and persisted retry history.
 */
class RetryStrategy
{
    public function __construct(
        private readonly RetryStrategyContract $backoffStrategy,
    ) {}

    public function decide(int $attempt, RetryConfigurationDTO $configuration): RetryDecisionDTO
    {
        $strategy = $configuration->strategy ?? $this->backoffStrategy;
        $shouldRetry = $strategy->shouldRetry($attempt, $configuration->maxAttempts);
        $nextAttempt = $attempt + 1;
        $delaySeconds = $shouldRetry ? $strategy->delaySeconds($nextAttempt) : 0;

        return new RetryDecisionDTO(
            shouldRetry: $shouldRetry,
            nextAttempt: $nextAttempt,
            delaySeconds: $delaySeconds,
            exhausted: ! $shouldRetry,
        );
    }

    /**
     * @param  array<string, mixed>|null  $error
     * @param  array<string, mixed>|null  $metadata
     */
    public function recordScheduledRetry(
        string $retryableType,
        string $retryableId,
        int $attempt,
        RetryConfigurationDTO $configuration,
        int $delaySeconds,
        ?string $tenantId = null,
        ?array $error = null,
        ?array $metadata = null,
    ): RetryHistory {
        $strategy = $configuration->strategy ?? $this->backoffStrategy;
        $scheduledAt = Carbon::now()->addSeconds($delaySeconds);

        return $this->record(new RecordRetryAttemptDTO(
            retryableType: $retryableType,
            retryableId: $retryableId,
            attempt: $attempt,
            maxAttempts: $configuration->maxAttempts,
            strategy: $strategy->identifier(),
            delaySeconds: $delaySeconds,
            status: RetryHistoryStatus::Scheduled,
            scheduledAt: $scheduledAt,
            tenantId: $tenantId,
            error: $error,
            metadata: $metadata,
        ));
    }

    public function record(RecordRetryAttemptDTO $dto): RetryHistory
    {
        return RetryHistory::query()->create([
            'tenant_id' => $dto->tenantId,
            'retryable_type' => $dto->retryableType,
            'retryable_id' => $dto->retryableId,
            'attempt' => $dto->attempt,
            'max_attempts' => $dto->maxAttempts,
            'strategy' => $dto->strategy,
            'delay_seconds' => $dto->delaySeconds,
            'status' => $dto->status,
            'error' => $dto->error,
            'metadata' => $dto->metadata,
            'scheduled_at' => $dto->scheduledAt,
            'attempted_at' => $dto->attemptedAt,
        ]);
    }

    public function markCompleted(RetryHistory $history): RetryHistory
    {
        $history->forceFill([
            'status' => RetryHistoryStatus::Completed,
            'attempted_at' => Carbon::now(),
        ])->save();

        return $history->fresh();
    }

    /**
     * @param  array<string, mixed>|null  $error
     */
    public function markFailed(RetryHistory $history, ?array $error = null): RetryHistory
    {
        $history->forceFill([
            'status' => RetryHistoryStatus::Failed,
            'error' => $error ?? $history->error,
            'attempted_at' => Carbon::now(),
        ])->save();

        return $history->fresh();
    }

    public function markExhausted(RetryHistory $history): RetryHistory
    {
        $history->forceFill([
            'status' => RetryHistoryStatus::Exhausted,
            'attempted_at' => Carbon::now(),
        ])->save();

        return $history->fresh();
    }

    /**
     * @return Collection<int, RetryHistory>
     */
    public function history(string $retryableType, string $retryableId): Collection
    {
        return RetryHistory::query()
            ->where('retryable_type', $retryableType)
            ->where('retryable_id', $retryableId)
            ->orderBy('attempt')
            ->get();
    }
}
