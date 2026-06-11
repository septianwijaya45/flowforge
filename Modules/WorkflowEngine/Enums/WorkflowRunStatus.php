<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Enums;

enum WorkflowRunStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case TimedOut = 'timed_out';
}
