<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Illuminate\Support\Facades\DB;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\InvalidStepConfigurationException;
use Throwable;

class DatabaseNodeExecutor implements WorkflowStepExecutorContract
{
    public function type(): WorkflowNodeType
    {
        return WorkflowNodeType::Database;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        $config = $command->node->config;
        $query = $config['query'] ?? null;

        if (! is_string($query) || trim($query) === '') {
            return WorkflowStepExecutionResultDTO::failed(
                $command->node->id,
                ['message' => InvalidStepConfigurationException::missingField($command->node->id, 'query')->getMessage()],
            );
        }

        if (! $this->isReadOnlyQuery($query)) {
            return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                'message' => "Workflow node [{$command->node->id}] only supports read-only SELECT queries.",
            ]);
        }

        $bindings = is_array($config['bindings'] ?? null) ? $config['bindings'] : [];
        $connection = is_string($config['connection'] ?? null) && trim($config['connection']) !== ''
            ? $config['connection']
            : null;

        try {
            $results = $connection === null
                ? DB::select($query, $bindings)
                : DB::connection($connection)->select($query, $bindings);

            $rows = array_map(static fn (object $row): array => (array) $row, $results);

            return WorkflowStepExecutionResultDTO::success($command->node->id, [
                'rows' => $rows,
                'count' => count($rows),
            ]);
        } catch (Throwable $throwable) {
            return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                'message' => $throwable->getMessage(),
                'exception' => $throwable::class,
            ]);
        }
    }

    private function isReadOnlyQuery(string $query): bool
    {
        $normalized = strtolower(trim($query));

        if (! str_starts_with($normalized, 'select')) {
            return false;
        }

        $forbidden = ['insert', 'update', 'delete', 'drop', 'alter', 'truncate', 'create', 'replace', 'grant', 'revoke'];

        foreach ($forbidden as $keyword) {
            if (preg_match('/\b'.preg_quote($keyword, '/').'\b/', $normalized) === 1) {
                return false;
            }
        }

        return true;
    }
}
