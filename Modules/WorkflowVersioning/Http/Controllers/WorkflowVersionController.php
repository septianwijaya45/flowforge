<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Exceptions\WorkflowValidationException;
use Modules\WorkflowVersioning\Contracts\WorkflowVersioningServiceContract;
use Modules\WorkflowVersioning\Exceptions\WorkflowVersioningException;
use Modules\WorkflowVersioning\Http\Requests\ListWorkflowVersionsRequest;
use Modules\WorkflowVersioning\Http\Requests\RollbackWorkflowVersionRequest;
use Modules\WorkflowVersioning\Http\Requests\StoreWorkflowVersionRequest;
use Modules\WorkflowVersioning\Http\Resources\WorkflowVersionResource;
use Symfony\Component\HttpFoundation\Response;

class WorkflowVersionController extends Controller
{
    public function __construct(
        private readonly WorkflowVersioningServiceContract $versioningService,
    ) {}

    public function index(ListWorkflowVersionsRequest $request, Workflow $workflow): JsonResponse
    {
        $paginator = $this->versioningService->history($workflow, $request->toDto());

        $versions = collect($paginator->items())
            ->map(fn (WorkflowVersion $version): array => (
                new WorkflowVersionResource($version, $workflow, includeDefinition: false)
            )->resolve())
            ->values()
            ->all();

        return ApiResponse::success([
            'versions' => $versions,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], 'Workflow version history retrieved');
    }

    public function store(StoreWorkflowVersionRequest $request, Workflow $workflow): JsonResponse
    {
        try {
            $version = $this->versioningService->createVersion($workflow, $request->toDto());
        } catch (WorkflowValidationException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(
            ['version' => (new WorkflowVersionResource($version, $workflow))->resolve()],
            'Workflow version created',
            Response::HTTP_CREATED,
        );
    }

    public function rollback(
        RollbackWorkflowVersionRequest $request,
        Workflow $workflow,
        WorkflowVersion $version,
    ): JsonResponse {
        try {
            $rolledBackVersion = $this->versioningService->rollback(
                $workflow,
                $version,
                $request->toDto(),
            );
        } catch (WorkflowVersioningException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::success(
            ['version' => (new WorkflowVersionResource($rolledBackVersion, $workflow))->resolve()],
            'Workflow rolled back',
        );
    }
}
