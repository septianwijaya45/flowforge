<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionEngineContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowRunDTO;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Throwable;

class ExecuteWorkflowRunJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $runId,
    ) {}

    public function handle(
        WorkflowExecutionEngineContract $engine,
        TenantContextContract $tenantContext,
    ): void {
        $run = WorkflowRun::query()
            ->withoutTenantScope()
            ->with('tenant')
            ->find($this->runId);

        if ($run === null || $run->tenant === null) {
            return;
        }

        $tenantContext->clear();
        $tenantContext->set($run->tenant);

        $engine->execute(new ExecuteWorkflowRunDTO($run->id));
    }

    public function failed(?Throwable $exception): void
    {
        report($exception);
    }
}
