<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorFactoryContract;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\WorkflowExecutionException;

/**
 * Resolves the appropriate step executor strategy for a workflow node type.
 */
class WorkflowStepExecutorFactory implements WorkflowStepExecutorFactoryContract
{
    /** @var array<string, WorkflowStepExecutorContract> */
    private array $executors;

    public function __construct(WorkflowStepExecutorContract ...$executors)
    {
        $this->executors = [];

        foreach ($executors as $executor) {
            $this->executors[$executor->type()->value] = $executor;
        }
    }

    public function make(WorkflowNodeType $type): WorkflowStepExecutorContract
    {
        $executor = $this->executors[$type->value] ?? null;

        if ($executor === null) {
            throw WorkflowExecutionException::unsupportedNodeType($type->value);
        }

        return $executor;
    }
}
