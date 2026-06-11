<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

class InvalidStepConfigurationException extends WorkflowExecutionException
{
    public static function missingField(string $nodeId, string $field): self
    {
        return new self("Workflow node [{$nodeId}] is missing required config [{$field}].");
    }

    public static function unsupportedOperator(string $nodeId, string $operator): self
    {
        return new self("Workflow node [{$nodeId}] uses unsupported condition operator [{$operator}].");
    }
}
