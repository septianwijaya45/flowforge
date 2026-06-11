<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Modules\WorkflowEngine\Contracts\WorkflowTopologicalSorterContract;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\Exceptions\CycleDetectedException;

/**
 * Computes parallel execution layers for a workflow DAG via Kahn's topological sort.
 */
class WorkflowTopologicalSorter implements WorkflowTopologicalSorterContract
{
    public function sort(WorkflowGraphDTO $graph): array
    {
        $nodeIds = array_map(
            static fn ($node): string => $node->id,
            $graph->nodes,
        );

        /** @var array<string, int> $inDegree */
        $inDegree = array_fill_keys($nodeIds, 0);

        /** @var array<string, list<string>> $outgoing */
        $outgoing = array_fill_keys($nodeIds, []);

        foreach ($graph->edges as $edge) {
            $inDegree[$edge->target]++;
            $outgoing[$edge->source][] = $edge->target;
        }

        $queue = array_values(array_filter(
            $nodeIds,
            static fn (string $nodeId): bool => $inDegree[$nodeId] === 0,
        ));

        sort($queue);

        /** @var list<list<string>> $layers */
        $layers = [];
        $processedCount = 0;

        while ($queue !== []) {
            $layer = $queue;
            sort($layer);
            $layers[] = $layer;
            $processedCount += count($layer);

            $nextQueue = [];

            foreach ($layer as $nodeId) {
                foreach ($outgoing[$nodeId] as $targetNodeId) {
                    $inDegree[$targetNodeId]--;

                    if ($inDegree[$targetNodeId] === 0) {
                        $nextQueue[] = $targetNodeId;
                    }
                }
            }

            sort($nextQueue);
            $queue = $nextQueue;
        }

        if ($processedCount !== count($nodeIds)) {
            throw new CycleDetectedException([]);
        }

        return $layers;
    }
}
