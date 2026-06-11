<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

use Modules\WorkflowEngine\Enums\WorkflowRunStatus;

final readonly class WorkflowExecutionResultDTO
{
    /**
     * @param  array<string, mixed>  $output
     * @param  array<string, mixed>|null  $error
     */
    public function __construct(
        public string $runId,
        public WorkflowRunStatus $status,
        public array $output = [],
        public ?string $failedNodeId = null,
        public ?array $error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $output
     */
    public static function success(string $runId, array $output = []): self
    {
        return new self(
            runId: $runId,
            status: WorkflowRunStatus::Success,
            output: $output,
        );
    }

    /**
     * @param  array<string, mixed>  $error
     */
    public static function failed(string $runId, string $failedNodeId, array $error): self
    {
        return new self(
            runId: $runId,
            status: WorkflowRunStatus::Failed,
            failedNodeId: $failedNodeId,
            error: $error,
        );
    }

    public static function cancelled(string $runId): self
    {
        return new self(
            runId: $runId,
            status: WorkflowRunStatus::Cancelled,
        );
    }
}
