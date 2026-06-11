<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\Enums\WorkflowNodeType;

interface WorkflowStepExecutorFactoryContract
{
    public function make(WorkflowNodeType $type): WorkflowStepExecutorContract;
}
