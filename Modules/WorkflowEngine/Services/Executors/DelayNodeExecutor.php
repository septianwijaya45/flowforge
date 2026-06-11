<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Modules\WorkflowEngine\Contracts\WorkflowNodeExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;

class DelayNodeExecutor implements WorkflowNodeExecutorContract
{
    public function supports(WorkflowNodeType $type): bool
    {
        return $type === WorkflowNodeType::Delay;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        return WorkflowStepExecutionResultDTO::success($command->node->id, [
            'type' => WorkflowNodeType::Delay->value,
            'seconds' => (int) ($command->node->config['seconds'] ?? 0),
        ]);
    }
}
