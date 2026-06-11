<?php

declare(strict_types=1);

namespace Modules\AI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\AI\Contracts\NaturalLanguageWorkflowBuilderContract;
use Modules\AI\Exceptions\LlmClientException;
use Modules\AI\Exceptions\WorkflowGenerationException;
use Modules\AI\Http\Requests\BuildWorkflowFromPromptRequest;
use Modules\Auth\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

final class NaturalLanguageWorkflowController extends Controller
{
    public function __construct(
        private readonly NaturalLanguageWorkflowBuilderContract $workflowBuilder,
    ) {}

    public function build(BuildWorkflowFromPromptRequest $request): JsonResponse
    {
        try {
            $result = $this->workflowBuilder->build($request->toDto());

            return ApiResponse::success(
                $result->toArray(),
                'Workflow definition generated',
                Response::HTTP_CREATED,
            );
        } catch (LlmClientException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                status: Response::HTTP_SERVICE_UNAVAILABLE,
            );
        } catch (WorkflowGenerationException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }
    }
}
