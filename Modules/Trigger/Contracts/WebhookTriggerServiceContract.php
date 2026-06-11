<?php

declare(strict_types=1);

namespace Modules\Trigger\Contracts;

use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\WorkflowEngine\Models\WorkflowRun;

interface WebhookTriggerServiceContract
{
    public function handle(string $token, DispatchTriggerDTO $dto): WorkflowRun;
}
