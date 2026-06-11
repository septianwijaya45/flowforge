<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Trigger\Contracts\WorkflowTriggerServiceContract;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Http\Requests\StoreWorkflowTriggerRequest;
use Modules\Trigger\Http\Requests\UpdateWorkflowTriggerRequest;
use Modules\Trigger\Http\Resources\WorkflowTriggerResource;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;
use Symfony\Component\HttpFoundation\Response;

class WorkflowTriggerController extends Controller
{
    public function __construct(
        private readonly WorkflowTriggerServiceContract $triggerService,
    ) {}

    public function index(Workflow $workflow): JsonResponse
    {
        $triggers = $this->triggerService->listForWorkflow($workflow);

        return ApiResponse::success([
            'triggers' => WorkflowTriggerResource::collection($triggers)->resolve(),
        ], 'Workflow triggers retrieved');
    }

    public function store(StoreWorkflowTriggerRequest $request, Workflow $workflow): JsonResponse
    {
        try {
            $trigger = $this->triggerService->create($workflow, $request->toDto());
        } catch (TriggerException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(
            ['trigger' => (new WorkflowTriggerResource($trigger))->resolve()],
            'Workflow trigger created',
            Response::HTTP_CREATED,
        );
    }

    public function update(
        UpdateWorkflowTriggerRequest $request,
        Workflow $workflow,
        WorkflowTrigger $trigger,
    ): JsonResponse {
        if ($trigger->workflow_id !== $workflow->id) {
            return ApiResponse::error('Trigger not found.', status: Response::HTTP_NOT_FOUND);
        }

        try {
            $trigger = $this->triggerService->update($trigger, $request->toDto());
        } catch (TriggerException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(
            ['trigger' => (new WorkflowTriggerResource($trigger))->resolve()],
            'Workflow trigger updated',
        );
    }

    public function destroy(Workflow $workflow, WorkflowTrigger $trigger): JsonResponse
    {
        if ($trigger->workflow_id !== $workflow->id) {
            return ApiResponse::error('Trigger not found.', status: Response::HTTP_NOT_FOUND);
        }

        $this->triggerService->delete($trigger);

        return ApiResponse::success(message: 'Workflow trigger deleted');
    }
}
