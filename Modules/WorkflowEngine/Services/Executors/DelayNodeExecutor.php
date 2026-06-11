<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Modules\WorkflowEngine\Contracts\DelaySleeperContract;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;

class DelayNodeExecutor implements WorkflowStepExecutorContract
{
    public function __construct(
        private readonly DelaySleeperContract $sleeper,
    ) {}

    public function type(): WorkflowNodeType
    {
        return WorkflowNodeType::Delay;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        $seconds = max(0, (int) ($command->node->config['seconds'] ?? 0));

        $this->sleeper->sleep($seconds);

        return WorkflowStepExecutionResultDTO::success($command->node->id, [
            'delayed_seconds' => $seconds,
        ]);
    }
}
