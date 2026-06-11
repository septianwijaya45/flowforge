<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Enums;

enum WorkflowRunStepStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case Cancelled = 'cancelled';
}
