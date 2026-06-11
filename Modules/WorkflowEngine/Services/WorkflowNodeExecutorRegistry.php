<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Modules\WorkflowEngine\Contracts\WorkflowNodeExecutorContract;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\WorkflowExecutionException;

class WorkflowNodeExecutorRegistry
{
    /**
     * @param  iterable<WorkflowNodeExecutorContract>  $executors
     */
    public function __construct(
        private readonly iterable $executors,
    ) {}

    public function resolve(WorkflowNodeType $type): WorkflowNodeExecutorContract
    {
        foreach ($this->executors as $executor) {
            if ($executor->supports($type)) {
                return $executor;
            }
        }

        throw WorkflowExecutionException::unsupportedNodeType($type->value);
    }
}
