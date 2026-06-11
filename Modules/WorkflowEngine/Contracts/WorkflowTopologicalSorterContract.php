<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;

interface WorkflowTopologicalSorterContract
{
    /**
     * Produce parallel execution layers for a validated DAG using Kahn's algorithm.
     *
     * @return list<list<string>>
     */
    public function sort(WorkflowGraphDTO $graph): array;
}
