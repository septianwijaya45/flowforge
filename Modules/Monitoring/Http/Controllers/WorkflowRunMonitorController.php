<?php

declare(strict_types=1);

namespace Modules\Monitoring\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Monitoring\Contracts\WorkflowRunMonitorServiceContract;
use Modules\Monitoring\Http\Requests\ListWorkflowRunsRequest;
use Modules\Monitoring\Http\Resources\WorkflowRunMonitorResource;
use Modules\WorkflowEngine\Models\WorkflowRun;

class WorkflowRunMonitorController extends Controller
{
    public function __construct(
        private readonly WorkflowRunMonitorServiceContract $monitorService,
    ) {}

    public function index(ListWorkflowRunsRequest $request): JsonResponse
    {
        $paginator = $this->monitorService->paginate($request->toDto());

        $runs = collect($paginator->items())
            ->map(fn (WorkflowRun $run): array => (
                new WorkflowRunMonitorResource($run)
            )->resolve())
            ->values()
            ->all();

        return ApiResponse::success([
            'runs' => $runs,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], 'Workflow runs retrieved');
    }

    public function show(WorkflowRun $run): JsonResponse
    {
        $run = $this->monitorService->show($run);

        return ApiResponse::success(
            ['run' => (new WorkflowRunMonitorResource($run))->resolve()],
            'Workflow run retrieved',
        );
    }
}
