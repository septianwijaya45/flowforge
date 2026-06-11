<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Executors;

use Illuminate\Support\Facades\Mail;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\InvalidStepConfigurationException;
use Modules\WorkflowEngine\Services\Support\WorkflowContextInterpolator;
use Throwable;

class EmailNodeExecutor implements WorkflowStepExecutorContract
{
    public function __construct(
        private readonly WorkflowContextInterpolator $interpolator,
    ) {}

    public function type(): WorkflowNodeType
    {
        return WorkflowNodeType::Email;
    }

    public function execute(ExecuteWorkflowNodeDTO $command): WorkflowStepExecutionResultDTO
    {
        $config = $command->node->config;
        $to = $config['to'] ?? null;

        if (! is_string($to) || trim($to) === '') {
            return WorkflowStepExecutionResultDTO::failed(
                $command->node->id,
                ['message' => InvalidStepConfigurationException::missingField($command->node->id, 'to')->getMessage()],
            );
        }

        $subject = $this->interpolator->interpolate(
            (string) ($config['subject'] ?? 'Notification'),
            $command->context,
        );

        $body = $this->interpolator->interpolate(
            (string) ($config['body'] ?? ''),
            $command->context,
        );

        try {
            Mail::raw($body, static function ($message) use ($to, $subject): void {
                $message->to($to)->subject($subject);
            });

            return WorkflowStepExecutionResultDTO::success($command->node->id, [
                'to' => $to,
                'subject' => $subject,
                'sent' => true,
            ]);
        } catch (Throwable $throwable) {
            return WorkflowStepExecutionResultDTO::failed($command->node->id, [
                'message' => $throwable->getMessage(),
                'exception' => $throwable::class,
            ]);
        }
    }
}
