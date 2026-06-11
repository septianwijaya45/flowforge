<?php

declare(strict_types=1);

namespace Modules\Workflow\Enums;

enum WorkflowStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
    case Disabled = 'disabled';
}
