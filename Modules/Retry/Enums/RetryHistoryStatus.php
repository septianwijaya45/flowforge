<?php

declare(strict_types=1);

namespace Modules\Retry\Enums;

enum RetryHistoryStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Failed = 'failed';
    case Exhausted = 'exhausted';
}
