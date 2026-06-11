<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Illuminate\Support\Facades\Http;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\InvalidStepConfigurationException;
use Modules\WorkflowEngine\Services\Support\WorkflowContextInterpolator;
use Throwable;

class WebhookNodeExecutor implements WorkflowStepExecutorContract
{
    public function __construct(
        private readonly WorkflowContextInterpolator $interpolator,
    ) {}

    public function type(): WorkflowNodeType
    {
        return WorkflowNodeType::Webhook;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        $config = $command->node->config;
        $url = $config['url'] ?? null;

        if (! is_string($url) || trim($url) === '') {
            return WorkflowStepExecutionResultDTO::failed(
                $command->node->id,
                ['message' => InvalidStepConfigurationException::missingField($command->node->id, 'url')->getMessage()],
            );
        }

        $url = $this->interpolator->interpolate($url, $command->context);
        $headers = is_array($config['headers'] ?? null) ? $config['headers'] : [];
        $timeout = (int) ($config['timeout'] ?? 30);
        $payload = $this->resolvePayload($command);

        try {
            $response = Http::timeout(max(1, $timeout))
                ->withHeaders($headers)
                ->post($url, is_array($payload) ? $payload : ['payload' => $payload]);

            if (! $response->successful()) {
                return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                    'message' => 'Webhook request failed.',
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
            }

            return WorkflowStepExecutionResultDTO::success($command->node->id, [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        } catch (Throwable $throwable) {
            return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                'message' => $throwable->getMessage(),
                'exception' => $throwable::class,
            ]);
        }
    }

    /**
     * @return array<string, mixed>|mixed
     */
    private function resolvePayload(ExecuteWorkflowNodeDTO $command): mixed
    {
        $config = $command->node->config;

        if (isset($config['payload']) && is_array($config['payload'])) {
            return $config['payload'];
        }

        $payloadPath = $config['payload_path'] ?? null;

        if (is_string($payloadPath) && trim($payloadPath) !== '') {
            return $this->interpolator->resolvePath($payloadPath, $command->context) ?? [];
        }

        return $command->context;
    }
}
