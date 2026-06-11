<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Illuminate\Support\Facades\Http;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\InvalidStepConfigurationException;
use Throwable;

class HttpNodeExecutor implements WorkflowStepExecutorContract
{
    public function type(): WorkflowNodeType
    {
        return WorkflowNodeType::Http;
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

        $method = strtoupper((string) ($config['method'] ?? 'GET'));
        $headers = is_array($config['headers'] ?? null) ? $config['headers'] : [];
        $body = $config['body'] ?? null;
        $timeout = (int) ($config['timeout'] ?? 30);

        try {
            $pendingRequest = Http::timeout(max(1, $timeout))->withHeaders($headers);

            $response = match ($method) {
                'GET' => $pendingRequest->get($url),
                'POST' => $pendingRequest->post($url, is_array($body) ? $body : []),
                'PUT' => $pendingRequest->put($url, is_array($body) ? $body : []),
                'PATCH' => $pendingRequest->patch($url, is_array($body) ? $body : []),
                'DELETE' => $pendingRequest->delete($url, is_array($body) ? $body : []),
                default => throw InvalidStepConfigurationException::missingField($command->node->id, 'method'),
            };

            if (! $response->successful()) {
                return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                    'message' => 'HTTP request failed.',
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
            }

            return WorkflowStepExecutionResultDTO::success($command->node->id, [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
            ]);
        } catch (Throwable $throwable) {
            return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                'message' => $throwable->getMessage(),
                'exception' => $throwable::class,
            ]);
        }
    }
}
