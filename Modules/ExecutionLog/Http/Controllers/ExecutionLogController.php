<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\ExecutionLog\Contracts\ExecutionLogServiceContract;
use Modules\ExecutionLog\Http\Requests\ListExecutionLogsRequest;
use Modules\ExecutionLog\Http\Resources\ExecutionLogResource;
use Modules\WorkflowEngine\Models\WorkflowRun;

class ExecutionLogController extends Controller
{
    public function __construct(
        private readonly ExecutionLogServiceContract $executionLogService,
    ) {}

    public function forRun(ListExecutionLogsRequest $request, WorkflowRun $run): JsonResponse
    {
        $logs = $this->executionLogService->forRun($run, $request->limit());

        return ApiResponse::success([
            'workflow_run_id' => $run->id,
            'logs' => ExecutionLogResource::collection($logs)->resolve(),
        ], 'Execution logs retrieved');
    }
}
