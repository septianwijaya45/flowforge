<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Modules\WorkflowEngine\Contracts\WorkflowNodeExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;

class ConditionNodeExecutor implements WorkflowNodeExecutorContract
{
    public function supports(WorkflowNodeType $type): bool
    {
        return $type === WorkflowNodeType::Condition;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        return WorkflowStepExecutionResultDTO::success($command->node->id, [
            'type' => WorkflowNodeType::Condition->value,
            'result' => (bool) ($command->node->config['result'] ?? true),
        ]);
    }
}
