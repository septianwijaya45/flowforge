<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

use Exception;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Throwable;

class WorkflowExecutionException extends Exception
{
    public static function runNotFound(string $runId): self
    {
        return new self("Workflow run [{$runId}] was not found.");
    }

    public static function invalidRunStatus(string $runId, WorkflowRunStatus $status): self
    {
        return new self("Workflow run [{$runId}] cannot be executed while in [{$status->value}] status.");
    }

    public static function unsupportedNodeType(string $type): self
    {
        return new self("No executor registered for workflow node type [{$type}].");
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        return new self($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
    }
}
