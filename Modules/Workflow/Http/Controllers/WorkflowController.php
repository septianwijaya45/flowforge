<?php

declare(strict_types=1);

namespace Modules\Workflow\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Workflow\Contracts\WorkflowServiceContract;
use Modules\Workflow\Http\Requests\ListWorkflowsRequest;
use Modules\Workflow\Http\Requests\StoreWorkflowRequest;
use Modules\Workflow\Http\Requests\UpdateWorkflowRequest;
use Modules\Workflow\Http\Resources\WorkflowResource;
use Modules\Workflow\Models\Workflow;
use Symfony\Component\HttpFoundation\Response;

class WorkflowController extends Controller
{
    public function __construct(
        private readonly WorkflowServiceContract $workflowService,
    ) {}

    public function index(ListWorkflowsRequest $request): JsonResponse
    {
        $paginator = $this->workflowService->paginate($request->toDto());

        return ApiResponse::success([
            'workflows' => WorkflowResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], 'Workflows retrieved');
    }

    public function show(Workflow $workflow): JsonResponse
    {
        return ApiResponse::success(
            ['workflow' => (new WorkflowResource($workflow))->resolve()],
            'Workflow retrieved',
        );
    }

    public function store(StoreWorkflowRequest $request): JsonResponse
    {
        $workflow = $this->workflowService->create($request->toDto());

        return ApiResponse::success(
            ['workflow' => (new WorkflowResource($workflow))->resolve()],
            'Workflow created',
            Response::HTTP_CREATED,
        );
    }

    public function update(UpdateWorkflowRequest $request, Workflow $workflow): JsonResponse
    {
        $workflow = $this->workflowService->update($workflow, $request->toDto());

        return ApiResponse::success(
            ['workflow' => (new WorkflowResource($workflow))->resolve()],
            'Workflow updated',
        );
    }

    public function destroy(Workflow $workflow): JsonResponse
    {
        $this->workflowService->delete($workflow);

        return ApiResponse::success(message: 'Workflow deleted');
    }
}
