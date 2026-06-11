<?php

declare(strict_types=1);

namespace Modules\Trigger\Services;

use Cron\CronExpression;
use DateTimeInterface;
use Modules\Trigger\Contracts\CronTriggerServiceContract;
use Modules\Trigger\Contracts\TriggerDispatcherContract;
use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\Trigger\DTOs\ProcessCronTriggersResultDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\WorkflowEngine\Models\WorkflowRun;

class CronTriggerService implements CronTriggerServiceContract
{
    public function __construct(
        private readonly TriggerDispatcherContract $dispatcher,
    ) {}

    public function isDue(WorkflowTrigger $trigger, DateTimeInterface $moment): bool
    {
        if ($trigger->type !== TriggerType::Cron || ! $trigger->is_active) {
            return false;
        }

        $expression = $trigger->config['expression'] ?? null;

        if (! is_string($expression)) {
            return false;
        }

        return (new CronExpression($expression))->isDue($moment);
    }

    public function processDueTriggers(?DateTimeInterface $moment = null): ProcessCronTriggersResultDTO
    {
        $moment ??= now();

        /** @var list<WorkflowRun> $runs */
        $runs = [];

        WorkflowTrigger::query()
            ->where('type', TriggerType::Cron)
            ->where('is_active', true)
            ->orderBy('id')
            ->each(function (WorkflowTrigger $trigger) use ($moment, &$runs): void {
                if (! $this->isDue($trigger, $moment)) {
                    return;
                }

                $runs[] = $this->dispatcher->dispatchFromTrigger($trigger, new DispatchTriggerDTO(
                    payload: [
                        'cron_expression' => $trigger->config['expression'] ?? null,
                        'scheduled_at' => $moment->format(DateTimeInterface::ATOM),
                    ],
                ));
            });

        return new ProcessCronTriggersResultDTO(
            runs: $runs,
            processedCount: count($runs),
        );
    }
}
