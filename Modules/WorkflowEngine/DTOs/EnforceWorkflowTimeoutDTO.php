<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

final readonly class EnforceWorkflowTimeoutDTO
{
    public function __construct(
        public string $runId,
        public int $timeoutSeconds,
        public ?string $reason = null,
    ) {
        if ($timeoutSeconds < 1) {
            throw new \InvalidArgumentException('timeoutSeconds must be at least 1.');
        }
    }
}
