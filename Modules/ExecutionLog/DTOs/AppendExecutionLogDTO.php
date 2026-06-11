<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\DTOs;

use DateTimeInterface;
use Modules\ExecutionLog\Enums\ExecutionLogLevel;

final readonly class AppendExecutionLogDTO
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        public string $tenantId,
        public ExecutionLogLevel $level,
        public string $message,
        public ?string $workflowId = null,
        public ?string $workflowRunId = null,
        public ?string $workflowRunStepId = null,
        public ?string $nodeId = null,
        public ?array $context = null,
        public ?DateTimeInterface $loggedAt = null,
    ) {}
}
