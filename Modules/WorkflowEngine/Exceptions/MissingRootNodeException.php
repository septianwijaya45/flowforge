<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

class MissingRootNodeException extends WorkflowValidationException
{
    public static function forEntryNodeId(string $entryNodeId): self
    {
        return new self(
            message: "Workflow entry node [{$entryNodeId}] does not exist.",
            errors: ['entry_node_id' => "Node [{$entryNodeId}] was not found in the graph."],
        );
    }

    public static function hasIncomingEdges(string $entryNodeId): self
    {
        return new self(
            message: "Workflow entry node [{$entryNodeId}] cannot have incoming edges.",
            errors: ['entry_node_id' => "Node [{$entryNodeId}] must be a root node with zero incoming edges."],
        );
    }

    /**
     * @param  list<string>  $nodeIds
     */
    public static function multipleCandidates(array $nodeIds): self
    {
        $listed = implode(', ', $nodeIds);

        return new self(
            message: 'Workflow graph has multiple root node candidates.',
            errors: ['entry_node_id' => "Expected a single root node, found: {$listed}."],
        );
    }
}
