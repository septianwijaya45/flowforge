<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

use Modules\WorkflowEngine\Exceptions\WorkflowValidationException;

final readonly class WorkflowGraphDTO
{
    /**
     * @param  list<WorkflowNodeDTO>  $nodes
     * @param  list<WorkflowEdgeDTO>  $edges
     */
    public function __construct(
        public array $nodes,
        public array $edges,
        public string $entryNodeId,
    ) {}

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function fromArray(array $definition): self
    {
        if (! isset($definition['nodes']) || ! is_array($definition['nodes'])) {
            throw WorkflowValidationException::withErrors(
                'Workflow definition must contain a nodes array.',
                ['nodes' => 'The nodes field is required and must be an array.'],
            );
        }

        if ($definition['nodes'] === []) {
            throw WorkflowValidationException::withErrors(
                'Workflow definition must contain at least one node.',
                ['nodes' => 'At least one node is required.'],
            );
        }

        if (! isset($definition['edges']) || ! is_array($definition['edges'])) {
            throw WorkflowValidationException::withErrors(
                'Workflow definition must contain an edges array.',
                ['edges' => 'The edges field is required and must be an array.'],
            );
        }

        if (! isset($definition['entry_node_id']) || ! is_string($definition['entry_node_id']) || trim($definition['entry_node_id']) === '') {
            throw WorkflowValidationException::withErrors(
                'Workflow definition must contain a valid entry_node_id.',
                ['entry_node_id' => 'The entry_node_id field is required and must be a non-empty string.'],
            );
        }

        $nodes = array_map(
            static fn (mixed $node): WorkflowNodeDTO => is_array($node)
                ? WorkflowNodeDTO::fromArray($node)
                : throw WorkflowValidationException::withErrors(
                    'Each workflow node must be an object.',
                    ['nodes' => 'Every node entry must be an array.'],
                ),
            $definition['nodes'],
        );

        $edges = array_map(
            static fn (mixed $edge): WorkflowEdgeDTO => is_array($edge)
                ? WorkflowEdgeDTO::fromArray($edge)
                : throw WorkflowValidationException::withErrors(
                    'Each workflow edge must be an object.',
                    ['edges' => 'Every edge entry must be an array.'],
                ),
            $definition['edges'],
        );

        return new self(
            nodes: array_values($nodes),
            edges: array_values($edges),
            entryNodeId: trim($definition['entry_node_id']),
        );
    }
}
