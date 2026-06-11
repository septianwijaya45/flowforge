<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\DTOs\ExecuteWorkflowRunDTO;
use Modules\WorkflowEngine\DTOs\WorkflowExecutionResultDTO;

interface WorkflowExecutionEngineContract
{
    public function execute(ExecuteWorkflowRunDTO $command): WorkflowExecutionResultDTO;
}
