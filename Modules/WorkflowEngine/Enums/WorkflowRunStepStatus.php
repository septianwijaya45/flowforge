<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Enums;

enum WorkflowRunStepStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
