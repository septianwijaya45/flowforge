<?php

declare(strict_types=1);

namespace Modules\Scheduler\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Scheduler\Contracts\ScheduleServiceContract;
use Modules\Scheduler\Http\Requests\StoreScheduleRequest;
use Modules\Scheduler\Http\Requests\UpdateScheduleRequest;
use Modules\Scheduler\Http\Resources\ScheduleResource;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Models\WorkflowTrigger;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleServiceContract $scheduleService,
    ) {}

    public function index(): JsonResponse
    {
        $schedules = $this->scheduleService->list();

        return ApiResponse::success([
            'schedules' => ScheduleResource::collection($schedules)->resolve(),
        ], 'Schedules retrieved');
    }

    public function show(WorkflowTrigger $schedule): JsonResponse
    {
        return ApiResponse::success(
            ['schedule' => (new ScheduleResource($schedule->load('workflow:id,name,slug')))->resolve()],
            'Schedule retrieved',
        );
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->create($request->toDto());
        } catch (TriggerException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(
            ['schedule' => (new ScheduleResource($schedule))->resolve()],
            'Schedule created',
            Response::HTTP_CREATED,
        );
    }

    public function update(UpdateScheduleRequest $request, WorkflowTrigger $schedule): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->update($schedule, $request->toDto());
        } catch (TriggerException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(
            ['schedule' => (new ScheduleResource($schedule))->resolve()],
            'Schedule updated',
        );
    }

    public function destroy(WorkflowTrigger $schedule): JsonResponse
    {
        try {
            $this->scheduleService->delete($schedule);
        } catch (TriggerException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(message: 'Schedule deleted');
    }
}
