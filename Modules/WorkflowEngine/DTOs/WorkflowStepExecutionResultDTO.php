<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;

final readonly class WorkflowStepExecutionResultDTO
{
    /**
     * @param  array<string, mixed>  $output
     * @param  array<string, mixed>|null  $error
     */
    public function __construct(
        public string $nodeId,
        public WorkflowRunStepStatus $status,
        public array $output = [],
        public ?array $error = null,
        public ?int $durationMs = null,
    ) {}

    /**
     * @param  array<string, mixed>  $output
     */
    public static function success(string $nodeId, array $output = [], ?int $durationMs = null): self
    {
        return new self(
            nodeId: $nodeId,
            status: WorkflowRunStepStatus::Success,
            output: $output,
            durationMs: $durationMs,
        );
    }

    /**
     * @param  array<string, mixed>  $error
     */
    public static function failed(string $nodeId, array $error, ?int $durationMs = null): self
    {
        return new self(
            nodeId: $nodeId,
            status: WorkflowRunStepStatus::Failed,
            error: $error,
            durationMs: $durationMs,
        );
    }

    public function isSuccessful(): bool
    {
        return $this->status === WorkflowRunStepStatus::Success;
    }
}
