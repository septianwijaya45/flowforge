<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

class MissingTerminalNodeException extends WorkflowValidationException
{
    public static function noneFound(): self
    {
        return new self(
            message: 'Workflow graph must contain at least one terminal node.',
            errors: ['nodes' => 'At least one node must have no outgoing edges.'],
        );
    }
}
