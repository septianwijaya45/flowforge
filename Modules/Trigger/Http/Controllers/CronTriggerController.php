<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Trigger\Contracts\CronTriggerServiceContract;
use Modules\Trigger\Http\Resources\WorkflowRunResource;

class CronTriggerController extends Controller
{
    public function __construct(
        private readonly CronTriggerServiceContract $cronTriggerService,
    ) {}

    public function process(): JsonResponse
    {
        $result = $this->cronTriggerService->processDueTriggers();

        return ApiResponse::success([
            'processed_count' => $result->processedCount,
            'runs' => collect($result->runs)
                ->map(fn ($run): array => (new WorkflowRunResource($run))->resolve())
                ->values()
                ->all(),
        ], 'Cron triggers processed');
    }
}
