<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Enums;

enum WorkflowTriggerType: string
{
    case Manual = 'manual';
    case Webhook = 'webhook';
    case Schedule = 'schedule';
    case Api = 'api';
}
