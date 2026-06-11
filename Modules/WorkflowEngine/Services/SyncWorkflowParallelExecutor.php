<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Modules\WorkflowEngine\Contracts\WorkflowParallelExecutorContract;

/**
 * Executes layer tasks sequentially.
 *
 * Swap this binding for a process/fork based executor in production when required.
 */
class SyncWorkflowParallelExecutor implements WorkflowParallelExecutorContract
{
    public function run(array $tasks): array
    {
        $results = [];

        foreach ($tasks as $task) {
            $results[] = $task();
        }

        return $results;
    }
}
