<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;

interface WorkflowNodeExecutorContract
{
    public function supports(WorkflowNodeType $type): bool;

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO;
}
