<?php

declare(strict_types=1);

use Modules\WorkflowEngine\Contracts\WorkflowTopologicalSorterContract;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\Exceptions\CycleDetectedException;
use Modules\WorkflowEngine\Services\WorkflowTopologicalSorter;

/**
 * @param  array<string, mixed>  $definition
 */
function sorterGraphFromDefinition(array $definition): WorkflowGraphDTO
{
    return WorkflowGraphDTO::fromArray($definition);
}

function workflowTopologicalSorter(): WorkflowTopologicalSorter
{
    return new WorkflowTopologicalSorter;
}

describe('WorkflowTopologicalSorter', function (): void {
    it('returns execution layers for a diamond graph', function (): void {
        $layers = workflowTopologicalSorter()->sort(sorterGraphFromDefinition([
            'entry_node_id' => 'A',
            'nodes' => [
                ['id' => 'A', 'type' => 'http', 'config' => []],
                ['id' => 'B', 'type' => 'delay', 'config' => []],
                ['id' => 'C', 'type' => 'condition', 'config' => []],
                ['id' => 'D', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'A', 'target' => 'B'],
                ['id' => 'e2', 'source' => 'A', 'target' => 'C'],
                ['id' => 'e3', 'source' => 'B', 'target' => 'D'],
                ['id' => 'e4', 'source' => 'C', 'target' => 'D'],
            ],
        ]));

        expect($layers)->toBe([
            ['A'],
            ['B', 'C'],
            ['D'],
        ]);
    });

    it('returns a single layer for a single-node graph', function (): void {
        $layers = workflowTopologicalSorter()->sort(sorterGraphFromDefinition([
            'entry_node_id' => 'only',
            'nodes' => [
                ['id' => 'only', 'type' => 'http', 'config' => []],
            ],
            'edges' => [],
        ]));

        expect($layers)->toBe([
            ['only'],
        ]);
    });

    it('returns one node per layer for a linear graph', function (): void {
        $layers = workflowTopologicalSorter()->sort(sorterGraphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'wait', 'type' => 'delay', 'config' => []],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'wait'],
                ['id' => 'e2', 'source' => 'wait', 'target' => 'finish'],
            ],
        ]));

        expect($layers)->toBe([
            ['start'],
            ['wait'],
            ['finish'],
        ]);
    });

    it('sorts nodes alphabetically within each layer', function (): void {
        $layers = workflowTopologicalSorter()->sort(sorterGraphFromDefinition([
            'entry_node_id' => 'root',
            'nodes' => [
                ['id' => 'root', 'type' => 'http', 'config' => []],
                ['id' => 'zulu', 'type' => 'delay', 'config' => []],
                ['id' => 'alpha', 'type' => 'condition', 'config' => []],
                ['id' => 'mike', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'root', 'target' => 'zulu'],
                ['id' => 'e2', 'source' => 'root', 'target' => 'alpha'],
                ['id' => 'e3', 'source' => 'root', 'target' => 'mike'],
            ],
        ]));

        expect($layers)->toBe([
            ['root'],
            ['alpha', 'mike', 'zulu'],
        ]);
    });

    it('returns staggered layers for branches with different depths', function (): void {
        $layers = workflowTopologicalSorter()->sort(sorterGraphFromDefinition([
            'entry_node_id' => 'root',
            'nodes' => [
                ['id' => 'root', 'type' => 'http', 'config' => []],
                ['id' => 'short', 'type' => 'delay', 'config' => []],
                ['id' => 'long_a', 'type' => 'condition', 'config' => []],
                ['id' => 'long_b', 'type' => 'script', 'config' => []],
                ['id' => 'sink', 'type' => 'http', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'root', 'target' => 'short'],
                ['id' => 'e2', 'source' => 'root', 'target' => 'long_a'],
                ['id' => 'e3', 'source' => 'long_a', 'target' => 'long_b'],
                ['id' => 'e4', 'source' => 'short', 'target' => 'sink'],
                ['id' => 'e5', 'source' => 'long_b', 'target' => 'sink'],
            ],
        ]));

        expect($layers)->toBe([
            ['root'],
            ['long_a', 'short'],
            ['long_b'],
            ['sink'],
        ]);
    });

    it('handles multiple independent roots in topological order', function (): void {
        $layers = workflowTopologicalSorter()->sort(sorterGraphFromDefinition([
            'entry_node_id' => 'a',
            'nodes' => [
                ['id' => 'a', 'type' => 'http', 'config' => []],
                ['id' => 'b', 'type' => 'delay', 'config' => []],
                ['id' => 'c', 'type' => 'condition', 'config' => []],
            ],
            'edges' => [],
        ]));

        expect($layers)->toHaveCount(1)
            ->and($layers[0])->toBe(['a', 'b', 'c']);
    });

    it('throws when the graph contains a cycle', function (): void {
        expect(fn () => workflowTopologicalSorter()->sort(sorterGraphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'a', 'type' => 'delay', 'config' => []],
                ['id' => 'b', 'type' => 'script', 'config' => []],
                ['id' => 'finish', 'type' => 'condition', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'a'],
                ['id' => 'e2', 'source' => 'a', 'target' => 'b'],
                ['id' => 'e3', 'source' => 'b', 'target' => 'a'],
                ['id' => 'e4', 'source' => 'b', 'target' => 'finish'],
            ],
        ])))->toThrow(CycleDetectedException::class);
    });

    it('is bound in the service container', function (): void {
        expect(app(WorkflowTopologicalSorterContract::class))
            ->toBeInstanceOf(WorkflowTopologicalSorter::class);
    });
});
