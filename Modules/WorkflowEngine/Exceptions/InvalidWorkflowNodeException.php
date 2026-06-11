<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

class InvalidWorkflowNodeException extends WorkflowValidationException
{
    public static function missingId(): self
    {
        return new self(
            message: 'Workflow node is missing a valid id.',
            errors: ['nodes' => 'Each node must include a non-empty id.'],
        );
    }

    public static function missingType(string $nodeId): self
    {
        return new self(
            message: "Workflow node [{$nodeId}] is missing a valid type.",
            errors: ['nodes' => "Node [{$nodeId}] must include a type."],
        );
    }

    public static function unsupportedType(string $nodeId, string $type): self
    {
        return new self(
            message: "Workflow node [{$nodeId}] has an unsupported type [{$type}].",
            errors: ['nodes' => "Node [{$nodeId}] type [{$type}] is not supported."],
        );
    }

    public static function invalidConfig(string $nodeId): self
    {
        return new self(
            message: "Workflow node [{$nodeId}] has an invalid config.",
            errors: ['nodes' => "Node [{$nodeId}] config must be an array."],
        );
    }

    public static function invalidPosition(string $nodeId): self
    {
        return new self(
            message: "Workflow node [{$nodeId}] has an invalid position.",
            errors: ['nodes' => "Node [{$nodeId}] position must be an array."],
        );
    }

    public static function duplicateId(string $nodeId): self
    {
        return new self(
            message: "Duplicate workflow node id [{$nodeId}] detected.",
            errors: ['nodes' => "Node id [{$nodeId}] must be unique."],
        );
    }
}
