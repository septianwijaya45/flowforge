<?php

declare(strict_types=1);

namespace Modules\Trigger\Services;

use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Models\Tenant;
use Modules\Trigger\Contracts\TriggerDispatcherContract;
use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Jobs\ExecuteWorkflowRunJob;
use Modules\WorkflowEngine\Models\WorkflowRun;

class TriggerDispatcher implements TriggerDispatcherContract
{
    public function __construct(
        private readonly TenantContextContract $tenantContext,
    ) {}

    public function dispatch(
        Workflow $workflow,
        WorkflowTriggerType $runTriggerType,
        DispatchTriggerDTO $dto,
    ): WorkflowRun {
        $workflow->refresh();

        if ($workflow->current_version_id === null) {
            throw TriggerException::workflowHasNoCurrentVersion($workflow->id);
        }

        $this->ensureTenantContext($workflow->tenant);

        $triggerPayload = $dto->payload ?? [];

        if ($dto->triggerId !== null) {
            $triggerPayload['trigger_id'] = $dto->triggerId;
        }

        $run = WorkflowRun::query()->create([
            'workflow_id' => $workflow->id,
            'workflow_version_id' => $workflow->current_version_id,
            'status' => WorkflowRunStatus::Pending,
            'trigger_type' => $runTriggerType,
            'trigger_payload' => $triggerPayload === [] ? null : $triggerPayload,
            'input' => $dto->input,
            'triggered_by' => $dto->triggeredBy,
        ]);

        ExecuteWorkflowRunJob::dispatch($run->id);

        return $run;
    }

    public function dispatchFromTrigger(WorkflowTrigger $trigger, DispatchTriggerDTO $dto): WorkflowRun
    {
        if (! $trigger->is_active) {
            throw TriggerException::triggerInactive($trigger->id);
        }

        $run = $this->dispatch(
            $trigger->workflow,
            $this->mapTriggerType($trigger->type),
            new DispatchTriggerDTO(
                input: $dto->input,
                payload: array_merge($dto->payload ?? [], [
                    'trigger_name' => $trigger->name,
                ]),
                triggeredBy: $dto->triggeredBy,
                triggerId: $trigger->id,
            ),
        );

        $trigger->update(['last_triggered_at' => now()]);

        return $run;
    }

    public function mapTriggerType(TriggerType $type): WorkflowTriggerType
    {
        return match ($type) {
            TriggerType::Manual => WorkflowTriggerType::Manual,
            TriggerType::Cron => WorkflowTriggerType::Schedule,
            TriggerType::Webhook => WorkflowTriggerType::Webhook,
        };
    }

    private function ensureTenantContext(Tenant $tenant): void
    {
        if (! $this->tenantContext->hasTenant()) {
            $this->tenantContext->set($tenant);
        }
    }
}
