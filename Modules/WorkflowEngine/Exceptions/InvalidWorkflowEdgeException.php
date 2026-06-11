<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

class InvalidWorkflowEdgeException extends WorkflowValidationException
{
    public static function missingId(): self
    {
        return new self(
            message: 'Workflow edge is missing a valid id.',
            errors: ['edges' => 'Each edge must include a non-empty id.'],
        );
    }

    public static function missingSource(string $edgeId): self
    {
        return new self(
            message: "Workflow edge [{$edgeId}] is missing a valid source.",
            errors: ['edges' => "Edge [{$edgeId}] must include a source node id."],
        );
    }

    public static function missingTarget(string $edgeId): self
    {
        return new self(
            message: "Workflow edge [{$edgeId}] is missing a valid target.",
            errors: ['edges' => "Edge [{$edgeId}] must include a target node id."],
        );
    }

    public static function invalidSourceHandle(string $edgeId): self
    {
        return new self(
            message: "Workflow edge [{$edgeId}] has an invalid source_handle.",
            errors: ['edges' => "Edge [{$edgeId}] source_handle must be a non-empty string."],
        );
    }

    public static function duplicateId(string $edgeId): self
    {
        return new self(
            message: "Duplicate workflow edge id [{$edgeId}] detected.",
            errors: ['edges' => "Edge id [{$edgeId}] must be unique."],
        );
    }

    public static function duplicateConnection(string $edgeId, string $source, string $target): self
    {
        return new self(
            message: "Duplicate workflow edge connection [{$source} -> {$target}] detected.",
            errors: ['edges' => "Edge [{$edgeId}] duplicates an existing connection from [{$source}] to [{$target}]."],
        );
    }

    public static function selfLoop(string $edgeId, string $nodeId): self
    {
        return new self(
            message: "Workflow edge [{$edgeId}] creates a self-loop on node [{$nodeId}].",
            errors: ['edges' => "Edge [{$edgeId}] cannot connect node [{$nodeId}] to itself."],
        );
    }

    public static function unknownSource(string $edgeId, string $source): self
    {
        return new self(
            message: "Workflow edge [{$edgeId}] references unknown source node [{$source}].",
            errors: ['edges' => "Edge [{$edgeId}] source [{$source}] does not exist."],
        );
    }

    public static function unknownTarget(string $edgeId, string $target): self
    {
        return new self(
            message: "Workflow edge [{$edgeId}] references unknown target node [{$target}].",
            errors: ['edges' => "Edge [{$edgeId}] target [{$target}] does not exist."],
        );
    }
}
