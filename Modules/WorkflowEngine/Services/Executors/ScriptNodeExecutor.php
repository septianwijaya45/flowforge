<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;

class ScriptNodeExecutor implements WorkflowStepExecutorContract
{
    public function type(): WorkflowNodeType
    {
        return WorkflowNodeType::Script;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        return WorkflowStepExecutionResultDTO::success($command->node->id, [
            'executed' => true,
        ]);
    }
}
