<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Enums;

enum WorkflowNodeType: string
{
    case Http = 'http';
    case Delay = 'delay';
    case Condition = 'condition';
    case Script = 'script';
}
