<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;

/**
 * Strategy contract for executing an individual workflow step node.
 */
interface WorkflowStepExecutorContract
{
    public function type(): WorkflowNodeType;

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO;
}
