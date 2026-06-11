<?php

declare(strict_types=1);

namespace Modules\Retry\DTOs;

use DateTimeInterface;
use Modules\Retry\Enums\RetryHistoryStatus;

final readonly class RecordRetryAttemptDTO
{
    /**
     * @param  array<string, mixed>|null  $error
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $retryableType,
        public string $retryableId,
        public int $attempt,
        public int $maxAttempts,
        public string $strategy,
        public int $delaySeconds,
        public RetryHistoryStatus $status,
        public DateTimeInterface $scheduledAt,
        public ?string $tenantId = null,
        public ?array $error = null,
        public ?array $metadata = null,
        public ?DateTimeInterface $attemptedAt = null,
    ) {}
}
