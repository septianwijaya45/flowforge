<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

final readonly class ExecuteWorkflowNodeDTO
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public WorkflowRun $run,
        public WorkflowRunStep $step,
        public WorkflowNodeDTO $node,
        public array $context = [],
    ) {}
}
