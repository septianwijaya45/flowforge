<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Support\ApiResponse;
use Modules\Trigger\Contracts\WebhookTriggerServiceContract;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Http\Requests\WebhookTriggerRequest;
use Modules\Trigger\Http\Resources\WorkflowRunResource;
use Symfony\Component\HttpFoundation\Response;

class WebhookTriggerController extends Controller
{
    public function __construct(
        private readonly WebhookTriggerServiceContract $webhookTriggerService,
    ) {}

    public function handle(WebhookTriggerRequest $request, string $token): JsonResponse
    {
        try {
            $run = $this->webhookTriggerService->handle($token, $request->toDto());
        } catch (TriggerException $exception) {
            return ApiResponse::error($exception->getMessage(), status: Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::success(
            ['run' => (new WorkflowRunResource($run))->resolve()],
            'Workflow triggered via webhook',
            Response::HTTP_CREATED,
        );
    }
}
