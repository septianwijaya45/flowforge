<?php

declare(strict_types=1);

namespace Modules\Trigger\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Trigger\Contracts\WorkflowTriggerServiceContract;
use Modules\Trigger\DTOs\CreateWorkflowTriggerDTO;
use Modules\Trigger\DTOs\UpdateWorkflowTriggerDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Trigger\Support\CronExpressionValidator;
use Modules\Workflow\Models\Workflow;

class WorkflowTriggerService implements WorkflowTriggerServiceContract
{
    /**
     * @return Collection<int, WorkflowTrigger>
     */
    public function listForWorkflow(Workflow $workflow): Collection
    {
        return WorkflowTrigger::query()
            ->where('workflow_id', $workflow->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    public function create(Workflow $workflow, CreateWorkflowTriggerDTO $dto): WorkflowTrigger
    {
        $this->validateConfig($dto->type, $dto->config);

        return WorkflowTrigger::query()->create([
            'workflow_id' => $workflow->id,
            'type' => $dto->type,
            'name' => $dto->name,
            'is_active' => $dto->isActive,
            'config' => $dto->config,
            'webhook_token' => $dto->type === TriggerType::Webhook
                ? Str::random(64)
                : null,
            'created_by' => $dto->createdBy,
        ]);
    }

    public function update(WorkflowTrigger $trigger, UpdateWorkflowTriggerDTO $dto): WorkflowTrigger
    {
        if ($dto->name !== null) {
            $trigger->name = $dto->name;
        }

        if ($dto->config !== null) {
            $this->validateConfig($trigger->type, $dto->config);
            $trigger->config = $dto->config;
        }

        if ($dto->isActive !== null) {
            $trigger->is_active = $dto->isActive;
        }

        $trigger->save();

        return $trigger->refresh();
    }

    public function delete(WorkflowTrigger $trigger): void
    {
        $trigger->delete();
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    private function validateConfig(TriggerType $type, ?array $config): void
    {
        if ($type !== TriggerType::Cron) {
            return;
        }

        $expression = $config['expression'] ?? null;

        if (! is_string($expression) || ! CronExpressionValidator::isValid($expression)) {
            throw TriggerException::invalidCronExpression(is_string($expression) ? $expression : '');
        }
    }
}
