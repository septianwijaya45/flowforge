<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\InvalidStepConfigurationException;

class ConditionalNodeExecutor implements WorkflowStepExecutorContract
{
    public function type(): WorkflowNodeType
    {
        return WorkflowNodeType::Condition;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        try {
            $result = $this->evaluate($command);

            return WorkflowStepExecutionResultDTO::success($command->node->id, [
                'result' => $result,
                'operator' => (string) ($command->node->config['operator'] ?? 'truthy'),
                'path' => $command->node->config['path'] ?? null,
            ]);
        } catch (InvalidStepConfigurationException $exception) {
            return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function evaluate(ExecuteWorkflowNodeDTO $command): bool
    {
        $config = $command->node->config;

        if (array_key_exists('result', $config)) {
            return (bool) $config['result'];
        }

        $operator = (string) ($config['operator'] ?? 'truthy');
        $left = data_get($command->context, (string) ($config['path'] ?? ''));
        $right = $config['value'] ?? null;

        return match ($operator) {
            'equals' => $left == $right,
            'not_equals' => $left != $right,
            'greater_than' => is_numeric($left) && is_numeric($right) && $left > $right,
            'less_than' => is_numeric($left) && is_numeric($right) && $left < $right,
            'contains' => is_string($left) && is_string($right) && str_contains($left, $right),
            'truthy' => (bool) $left,
            'falsy' => ! (bool) $left,
            default => throw InvalidStepConfigurationException::unsupportedOperator(
                $command->node->id,
                $operator,
            ),
        };
    }
}
