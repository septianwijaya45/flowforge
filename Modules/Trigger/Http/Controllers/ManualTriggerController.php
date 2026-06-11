<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Trigger\Contracts\ManualTriggerServiceContract;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Http\Requests\FireManualTriggerRequest;
use Modules\Trigger\Http\Resources\WorkflowRunResource;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;
use Symfony\Component\HttpFoundation\Response;

class ManualTriggerController extends Controller
{
    public function __construct(
        private readonly ManualTriggerServiceContract $manualTriggerService,
    ) {}

    public function fire(FireManualTriggerRequest $request, Workflow $workflow): JsonResponse
    {
        $dto = $request->toDto();
        $trigger = null;

        if ($dto->triggerId !== null) {
            $trigger = WorkflowTrigger::query()
                ->whereKey($dto->triggerId)
                ->where('workflow_id', $workflow->id)
                ->first();

            if ($trigger === null) {
                return ApiResponse::error('Trigger not found.', status: Response::HTTP_NOT_FOUND);
            }
        }

        try {
            $run = $this->manualTriggerService->fire($workflow, $dto, $trigger);
        } catch (TriggerException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(
            ['run' => (new WorkflowRunResource($run))->resolve()],
            'Workflow triggered manually',
            Response::HTTP_CREATED,
        );
    }
}
