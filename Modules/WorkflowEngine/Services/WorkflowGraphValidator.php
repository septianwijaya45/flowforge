<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\DTOs\WorkflowEdgeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\DTOs\WorkflowNodeDTO;
use Modules\WorkflowEngine\Exceptions\CycleDetectedException;
use Modules\WorkflowEngine\Exceptions\InvalidWorkflowEdgeException;
use Modules\WorkflowEngine\Exceptions\InvalidWorkflowNodeException;
use Modules\WorkflowEngine\Exceptions\MissingRootNodeException;
use Modules\WorkflowEngine\Exceptions\MissingTerminalNodeException;
use Modules\WorkflowEngine\Exceptions\WorkflowValidationException;

/**
 * Validates workflow DAG definitions for structural correctness before persistence or execution.
 */
class WorkflowGraphValidator implements WorkflowGraphValidatorContract
{
    public function validate(WorkflowGraphDTO $graph): void
    {
        $nodeIds = $this->validateNodes($graph->nodes);
        $adjacency = $this->validateEdges($graph->edges, $nodeIds);

        $this->validateRootNode($graph, $adjacency['incoming']);
        $this->validateTerminalNode($nodeIds, $adjacency['outgoing']);
        $this->detectCycles($nodeIds, $adjacency['outgoing']);
        $this->validateReachability($graph->entryNodeId, $nodeIds, $adjacency['outgoing']);
    }

    /**
     * @param  list<WorkflowNodeDTO>  $nodes
     * @return array<string, true>
     */
    private function validateNodes(array $nodes): array
    {
        $nodeIds = [];

        foreach ($nodes as $node) {
            if (isset($nodeIds[$node->id])) {
                throw InvalidWorkflowNodeException::duplicateId($node->id);
            }

            $nodeIds[$node->id] = true;
        }

        return $nodeIds;
    }

    /**
     * @param  list<WorkflowEdgeDTO>  $edges
     * @param  array<string, true>  $nodeIds
     * @return array{incoming: array<string, list<string>>, outgoing: array<string, list<string>>}
     */
    private function validateEdges(array $edges, array $nodeIds): array
    {
        $incoming = [];
        $outgoing = [];
        $edgeIds = [];
        $connections = [];

        foreach (array_keys($nodeIds) as $nodeId) {
            $incoming[$nodeId] = [];
            $outgoing[$nodeId] = [];
        }

        foreach ($edges as $edge) {
            if (isset($edgeIds[$edge->id])) {
                throw InvalidWorkflowEdgeException::duplicateId($edge->id);
            }

            $edgeIds[$edge->id] = true;

            if ($edge->source === $edge->target) {
                throw InvalidWorkflowEdgeException::selfLoop($edge->id, $edge->source);
            }

            if (! isset($nodeIds[$edge->source])) {
                throw InvalidWorkflowEdgeException::unknownSource($edge->id, $edge->source);
            }

            if (! isset($nodeIds[$edge->target])) {
                throw InvalidWorkflowEdgeException::unknownTarget($edge->id, $edge->target);
            }

            $connectionKey = $edge->source.'|'.$edge->target;

            if (isset($connections[$connectionKey])) {
                throw InvalidWorkflowEdgeException::duplicateConnection($edge->id, $edge->source, $edge->target);
            }

            $connections[$connectionKey] = true;
            $outgoing[$edge->source][] = $edge->target;
            $incoming[$edge->target][] = $edge->source;
        }

        return [
            'incoming' => $incoming,
            'outgoing' => $outgoing,
        ];
    }

    /**
     * @param  array<string, list<string>>  $incoming
     */
    private function validateRootNode(WorkflowGraphDTO $graph, array $incoming): void
    {
        if (! isset($incoming[$graph->entryNodeId])) {
            throw MissingRootNodeException::forEntryNodeId($graph->entryNodeId);
        }

        if ($incoming[$graph->entryNodeId] !== []) {
            throw MissingRootNodeException::hasIncomingEdges($graph->entryNodeId);
        }

        $rootCandidates = array_values(array_filter(
            array_keys($incoming),
            static fn (string $nodeId): bool => $incoming[$nodeId] === [],
        ));

        if (count($rootCandidates) > 1) {
            throw MissingRootNodeException::multipleCandidates($rootCandidates);
        }
    }

    /**
     * @param  array<string, true>  $nodeIds
     * @param  array<string, list<string>>  $outgoing
     */
    private function validateTerminalNode(array $nodeIds, array $outgoing): void
    {
        foreach (array_keys($nodeIds) as $nodeId) {
            if ($outgoing[$nodeId] === []) {
                return;
            }
        }

        throw MissingTerminalNodeException::noneFound();
    }

    /**
     * @param  array<string, true>  $nodeIds
     * @param  array<string, list<string>>  $outgoing
     */
    private function detectCycles(array $nodeIds, array $outgoing): void
    {
        $visited = [];
        $visiting = [];
        $path = [];

        foreach (array_keys($nodeIds) as $nodeId) {
            if (! isset($visited[$nodeId])) {
                $this->depthFirstSearch($nodeId, $outgoing, $visited, $visiting, $path);
            }
        }
    }

    /**
     * @param  array<string, list<string>>  $outgoing
     * @param  array<string, true>  $visited
     * @param  array<string, true>  $visiting
     * @param  list<string>  $path
     */
    private function depthFirstSearch(
        string $nodeId,
        array $outgoing,
        array &$visited,
        array &$visiting,
        array &$path,
    ): void {
        $visited[$nodeId] = true;
        $visiting[$nodeId] = true;
        $path[] = $nodeId;

        foreach ($outgoing[$nodeId] as $targetNodeId) {
            if (isset($visiting[$targetNodeId])) {
                $cycleStart = array_search($targetNodeId, $path, true);
                $cyclePath = $cycleStart === false
                    ? [$targetNodeId]
                    : array_merge(array_slice($path, $cycleStart), [$targetNodeId]);

                throw new CycleDetectedException($cyclePath);
            }

            if (! isset($visited[$targetNodeId])) {
                $this->depthFirstSearch($targetNodeId, $outgoing, $visited, $visiting, $path);
            }
        }

        unset($visiting[$nodeId]);
        array_pop($path);
    }

    /**
     * @param  array<string, true>  $nodeIds
     * @param  array<string, list<string>>  $outgoing
     */
    private function validateReachability(string $entryNodeId, array $nodeIds, array $outgoing): void
    {
        $reachable = [];
        $queue = [$entryNodeId];

        while ($queue !== []) {
            $current = array_shift($queue);

            if (isset($reachable[$current])) {
                continue;
            }

            $reachable[$current] = true;

            foreach ($outgoing[$current] as $targetNodeId) {
                if (! isset($reachable[$targetNodeId])) {
                    $queue[] = $targetNodeId;
                }
            }
        }

        $unreachable = array_values(array_diff(array_keys($nodeIds), array_keys($reachable)));

        if ($unreachable !== []) {
            throw WorkflowValidationException::withErrors(
                'Workflow graph contains unreachable nodes.',
                ['nodes' => 'The following nodes are unreachable from the entry node: '.implode(', ', $unreachable).'.'],
            );
        }
    }
}
