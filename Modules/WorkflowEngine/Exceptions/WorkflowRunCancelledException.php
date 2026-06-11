<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

class WorkflowRunCancelledException extends WorkflowExecutionException
{
    public static function forRun(string $runId): self
    {
        return new self("Workflow run [{$runId}] has been cancelled.");
    }
}
