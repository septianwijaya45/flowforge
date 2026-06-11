<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\Exceptions\WorkflowValidationException;
use Throwable;

final class WorkflowDefinitionAiValidator
{
    public function __construct(
        private readonly WorkflowGraphValidatorContract $graphValidator,
    ) {}

    /**
     * @param  array<string, mixed>  $definition
     *
     * @throws WorkflowValidationException
     */
    public function validate(array $definition): WorkflowGraphDTO
    {
        $graph = WorkflowGraphDTO::fromArray($definition);
        $this->graphValidator->validate($graph);

        return $graph;
    }

    public function validationMessage(Throwable $throwable): string
    {
        if ($throwable instanceof WorkflowValidationException) {
            $errors = $throwable->errors;

            if ($errors !== []) {
                return $throwable->getMessage().' '.json_encode($errors, JSON_UNESCAPED_UNICODE);
            }
        }

        return $throwable->getMessage();
    }
}
