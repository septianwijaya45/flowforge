<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Enums;

enum ExecutionLogLevel: string
{
    case Debug = 'debug';
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
}
