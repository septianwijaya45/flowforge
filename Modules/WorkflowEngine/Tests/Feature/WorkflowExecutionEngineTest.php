<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionEngineContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionStatePersisterContract;
use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\Contracts\WorkflowNodeExecutorContract;
use Modules\WorkflowEngine\Contracts\WorkflowParallelExecutorContract;
use Modules\WorkflowEngine\Contracts\WorkflowTopologicalSorterContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowRunDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Exceptions\WorkflowRunCancelledException;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Services\Executors\ScriptNodeExecutor;
use Modules\WorkflowEngine\Services\WorkflowExecutionEngine;
use Modules\WorkflowEngine\Services\WorkflowNodeExecutorRegistry;

uses(RefreshDatabase::class);

final class LayerBatchTracker
{
    /** @var list<int> */
    public array $layerBatchSizes = [];
}

/**
 * @return array<string, mixed>
 */
function diamondWorkflowDefinition(): array
{
    return [
        'entry_node_id' => 'A',
        'nodes' => [
            ['id' => 'A', 'type' => 'http', 'config' => ['url' => 'https://example.com']],
            ['id' => 'B', 'type' => 'delay', 'config' => ['seconds' => 1]],
            ['id' => 'C', 'type' => 'condition', 'config' => ['result' => true]],
            ['id' => 'D', 'type' => 'script', 'config' => []],
        ],
        'edges' => [
            ['id' => 'e1', 'source' => 'A', 'target' => 'B'],
            ['id' => 'e2', 'source' => 'A', 'target' => 'C'],
            ['id' => 'e3', 'source' => 'B', 'target' => 'D'],
            ['id' => 'e4', 'source' => 'C', 'target' => 'D'],
        ],
    ];
}

/**
 * @param  array<string, mixed>|null  $definition
 */
function createPendingWorkflowRun(?array $definition = null): WorkflowRun
{
    $definition ??= diamondWorkflowDefinition();
    $tenant = Tenant::query()->create([
        'name' => 'Acme Corp',
        'slug' => 'acme-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Onboarding',
        'slug' => 'onboarding-'.uniqid(),
        'status' => WorkflowStatus::Active,
    ]);

    $version = WorkflowVersion::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflow->id,
        'version_number' => 1,
        'definition' => $definition,
    ]);

    return WorkflowRun::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Pending,
        'trigger_type' => WorkflowTriggerType::Manual,
        'input' => ['seed' => true],
    ]);
}

describe('WorkflowExecutionEngine', function (): void {
    it('executes layers sequentially and persists success state', function (): void {
        $run = createPendingWorkflowRun();

        $result = app(WorkflowExecutionEngineContract::class)->execute(
            new ExecuteWorkflowRunDTO($run->id),
        );

        expect($result->status)->toBe(WorkflowRunStatus::Success)
            ->and($result->output)->toHaveKey('D');

        $run->refresh();

        expect($run->status)->toBe(WorkflowRunStatus::Success)
            ->and($run->started_at)->not->toBeNull()
            ->and($run->completed_at)->not->toBeNull();

        $steps = $run->steps()->orderBy('execution_order')->get();

        expect($steps)->toHaveCount(4)
            ->and($steps->pluck('status')->unique()->all())->toBe([WorkflowRunStepStatus::Success])
            ->and($steps->pluck('node_id')->all())->toBe(['A', 'B', 'C', 'D'])
            ->and($steps[0]->execution_order)->toBe(0)
            ->and($steps[3]->execution_order)->toBe(3);
    });

    it('executes all nodes in a layer through the parallel executor', function (): void {
        $run = createPendingWorkflowRun();
        $tracker = new LayerBatchTracker;

        app()->forgetInstance(WorkflowExecutionEngineContract::class);
        app()->instance(WorkflowParallelExecutorContract::class, new class($tracker) implements WorkflowParallelExecutorContract
        {
            public function __construct(private LayerBatchTracker $tracker) {}

            public function run(array $tasks): array
            {
                $this->tracker->layerBatchSizes[] = count($tasks);

                return array_map(static fn (callable $task) => $task(), $tasks);
            }
        });

        app(WorkflowExecutionEngineContract::class)->execute(new ExecuteWorkflowRunDTO($run->id));

        expect($tracker->layerBatchSizes)->toBe([1, 2, 1]);
    });

    it('marks the run and step as failed when a node executor fails', function (): void {
        $run = createPendingWorkflowRun([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => ['fail' => true]],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'finish'],
            ],
        ]);

        app()->forgetInstance(WorkflowExecutionEngineContract::class);
        app()->instance(WorkflowExecutionEngineContract::class, new WorkflowExecutionEngine(
            app(WorkflowGraphValidatorContract::class),
            app(WorkflowTopologicalSorterContract::class),
            app(WorkflowParallelExecutorContract::class),
            new WorkflowNodeExecutorRegistry([
                new class implements WorkflowNodeExecutorContract
                {
                    public function supports(WorkflowNodeType $type): bool
                    {
                        return $type === WorkflowNodeType::Http;
                    }

                    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
                    {
                        return WorkflowStepExecutionResultDTO::failed(
                            $command->node->id,
                            ['message' => 'Simulated HTTP failure'],
                        );
                    }
                },
                app(ScriptNodeExecutor::class),
            ]),
            app(WorkflowExecutionStatePersisterContract::class),
        ));

        $result = app(WorkflowExecutionEngineContract::class)->execute(new ExecuteWorkflowRunDTO($run->id));

        expect($result->status)->toBe(WorkflowRunStatus::Failed)
            ->and($result->failedNodeId)->toBe('start');

        $run->refresh();

        expect($run->status)->toBe(WorkflowRunStatus::Failed);

        $startStep = $run->steps()->where('node_id', 'start')->first();

        expect($startStep?->status)->toBe(WorkflowRunStepStatus::Failed);

        $finishStep = $run->steps()->where('node_id', 'finish')->first();

        expect($finishStep?->status)->toBe(WorkflowRunStepStatus::Pending);
    });

    it('returns cancelled when the run was cancelled before execution starts', function (): void {
        $run = createPendingWorkflowRun();
        $run->update(['status' => WorkflowRunStatus::Cancelled]);

        expect(fn () => app(WorkflowExecutionEngineContract::class)->execute(
            new ExecuteWorkflowRunDTO($run->id),
        ))->toThrow(WorkflowRunCancelledException::class);
    });

    it('is bound in the service container', function (): void {
        expect(app(WorkflowExecutionEngineContract::class))
            ->toBeInstanceOf(WorkflowExecutionEngine::class);
    });
});
