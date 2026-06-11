<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;

interface WorkflowGraphValidatorContract
{
    public function validate(WorkflowGraphDTO $graph): void;
}
