<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Contracts\WorkflowTimeoutManagerContract;
use Modules\WorkflowEngine\DTOs\EnforceWorkflowTimeoutDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;
use Modules\WorkflowEngine\Services\WorkflowTimeoutManager;

uses(RefreshDatabase::class);

function createRunningWorkflowRunWithSteps(): WorkflowRun
{
    $tenant = Tenant::query()->create([
        'name' => 'Timeout Tenant',
        'slug' => 'timeout-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Timeout Workflow',
        'slug' => 'timeout-workflow-'.uniqid(),
        'status' => WorkflowStatus::Active,
    ]);

    $version = WorkflowVersion::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflow->id,
        'version_number' => 1,
        'definition' => ['entry_node_id' => 'A', 'nodes' => [], 'edges' => []],
    ]);

    $run = WorkflowRun::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Running,
        'trigger_type' => WorkflowTriggerType::Manual,
        'started_at' => Carbon::parse('2026-06-11 10:00:00'),
    ]);

    WorkflowRunStep::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_run_id' => $run->id,
        'node_id' => 'A',
        'node_type' => WorkflowNodeType::Http,
        'status' => WorkflowRunStepStatus::Success,
        'execution_order' => 0,
        'started_at' => Carbon::parse('2026-06-11 10:00:00'),
        'completed_at' => Carbon::parse('2026-06-11 10:00:05'),
    ]);

    WorkflowRunStep::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_run_id' => $run->id,
        'node_id' => 'B',
        'node_type' => WorkflowNodeType::Delay,
        'status' => WorkflowRunStepStatus::Running,
        'execution_order' => 1,
        'started_at' => Carbon::parse('2026-06-11 10:00:06'),
    ]);

    WorkflowRunStep::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_run_id' => $run->id,
        'node_id' => 'C',
        'node_type' => WorkflowNodeType::Script,
        'status' => WorkflowRunStepStatus::Pending,
        'execution_order' => 2,
    ]);

    return $run->fresh(['steps']);
}

describe('WorkflowTimeoutManager', function (): void {
    afterEach(function (): void {
        Carbon::setTestNow();
    });

    it('detects when a running workflow has exceeded its timeout', function (): void {
        $run = createRunningWorkflowRunWithSteps();
        $manager = app(WorkflowTimeoutManagerContract::class);

        $now = Carbon::parse('2026-06-11 10:05:01');

        expect($manager->shouldTimeout($run, 300, $now))->toBeTrue()
            ->and($manager->shouldTimeout($run, 300, Carbon::parse('2026-06-11 10:04:59')))->toBeFalse();
    });

    it('does not timeout workflows that are not running', function (): void {
        $run = createRunningWorkflowRunWithSteps();
        $run->update(['status' => WorkflowRunStatus::Success]);

        expect(app(WorkflowTimeoutManagerContract::class)->shouldTimeout($run->fresh(), 60))->toBeFalse();
    });

    it('cancels running and pending steps when enforcing a timeout', function (): void {
        Carbon::setTestNow(Carbon::parse('2026-06-11 10:10:00'));

        $run = createRunningWorkflowRunWithSteps();

        $result = app(WorkflowTimeoutManagerContract::class)->enforce(
            new EnforceWorkflowTimeoutDTO(
                runId: $run->id,
                timeoutSeconds: 300,
                reason: 'Run exceeded SLA',
            ),
        );

        expect($result->timedOut)->toBeTrue()
            ->and($result->status)->toBe(WorkflowRunStatus::TimedOut)
            ->and($result->cancelledStepsCount)->toBe(2);

        $run->refresh();

        expect($run->status)->toBe(WorkflowRunStatus::TimedOut)
            ->and($run->completed_at)->not->toBeNull()
            ->and($run->error)->toMatchArray([
                'message' => 'Run exceeded SLA',
                'code' => 'workflow_timeout',
                'timeout_seconds' => 300,
            ]);

        $steps = $run->steps()->orderBy('execution_order')->get();

        expect($steps[0]->status)->toBe(WorkflowRunStepStatus::Success)
            ->and($steps[1]->status)->toBe(WorkflowRunStepStatus::Cancelled)
            ->and($steps[2]->status)->toBe(WorkflowRunStepStatus::Cancelled)
            ->and($steps[1]->error['code'])->toBe('workflow_timeout');
    });

    it('returns without changes when the workflow has not timed out yet', function (): void {
        Carbon::setTestNow(Carbon::parse('2026-06-11 10:04:00'));

        $run = createRunningWorkflowRunWithSteps();

        $result = app(WorkflowTimeoutManagerContract::class)->enforce(
            new EnforceWorkflowTimeoutDTO($run->id, 300),
        );

        expect($result->timedOut)->toBeFalse()
            ->and($result->cancelledStepsCount)->toBe(0);

        $run->refresh();

        expect($run->status)->toBe(WorkflowRunStatus::Running);
    });

    it('is idempotent for already terminal workflow runs', function (): void {
        $run = createRunningWorkflowRunWithSteps();
        $run->update([
            'status' => WorkflowRunStatus::TimedOut,
            'completed_at' => Carbon::now(),
        ]);

        $result = app(WorkflowTimeoutManagerContract::class)->enforce(
            new EnforceWorkflowTimeoutDTO($run->id, 60),
        );

        expect($result->timedOut)->toBeTrue()
            ->and($result->cancelledStepsCount)->toBe(0);
    });

    it('rejects invalid timeout configuration', function (): void {
        expect(fn () => new EnforceWorkflowTimeoutDTO('run-id', 0))
            ->toThrow(InvalidArgumentException::class);
    });

    it('is bound in the service container', function (): void {
        expect(app(WorkflowTimeoutManagerContract::class))
            ->toBeInstanceOf(WorkflowTimeoutManager::class);
    });
});
