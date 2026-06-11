<?php

declare(strict_types=1);

namespace Modules\Trigger\Services;

use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Trigger\Contracts\TriggerDispatcherContract;
use Modules\Trigger\Contracts\WebhookTriggerServiceContract;
use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\WorkflowEngine\Models\WorkflowRun;

class WebhookTriggerService implements WebhookTriggerServiceContract
{
    public function __construct(
        private readonly TriggerDispatcherContract $dispatcher,
        private readonly TenantContextContract $tenantContext,
    ) {}

    public function handle(string $token, DispatchTriggerDTO $dto): WorkflowRun
    {
        $trigger = WorkflowTrigger::query()
            ->where('webhook_token', $token)
            ->where('type', TriggerType::Webhook)
            ->where('is_active', true)
            ->first();

        if ($trigger === null) {
            throw TriggerException::webhookNotFound();
        }

        $this->tenantContext->set($trigger->tenant);

        return $this->dispatcher->dispatchFromTrigger($trigger, new DispatchTriggerDTO(
            input: $dto->input,
            payload: array_merge($dto->payload ?? [], [
                'webhook_token' => $token,
            ]),
            triggeredBy: null,
        ));
    }
}
