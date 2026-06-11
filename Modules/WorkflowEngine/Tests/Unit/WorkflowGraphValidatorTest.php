<?php

declare(strict_types=1);

use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\Exceptions\CycleDetectedException;
use Modules\WorkflowEngine\Exceptions\InvalidWorkflowEdgeException;
use Modules\WorkflowEngine\Exceptions\InvalidWorkflowNodeException;
use Modules\WorkflowEngine\Exceptions\MissingRootNodeException;
use Modules\WorkflowEngine\Exceptions\MissingTerminalNodeException;
use Modules\WorkflowEngine\Exceptions\WorkflowValidationException;
use Modules\WorkflowEngine\Services\WorkflowGraphValidator;

/**
 * @return array<string, mixed>
 */
function validWorkflowDefinition(): array
{
    return [
        'entry_node_id' => 'start',
        'nodes' => [
            ['id' => 'start', 'type' => 'http', 'config' => ['url' => 'https://example.com']],
            ['id' => 'wait', 'type' => 'delay', 'config' => ['seconds' => 5]],
            ['id' => 'finish', 'type' => 'script', 'config' => []],
        ],
        'edges' => [
            ['id' => 'e1', 'source' => 'start', 'target' => 'wait'],
            ['id' => 'e2', 'source' => 'wait', 'target' => 'finish'],
        ],
    ];
}

function workflowGraphValidator(): WorkflowGraphValidator
{
    return new WorkflowGraphValidator;
}

/**
 * @param  array<string, mixed>  $definition
 */
function graphFromDefinition(array $definition): WorkflowGraphDTO
{
    return WorkflowGraphDTO::fromArray($definition);
}

describe('WorkflowGraphDTO', function (): void {
    it('builds a graph from a valid definition', function (): void {
        $graph = graphFromDefinition(validWorkflowDefinition());

        expect($graph->entryNodeId)->toBe('start')
            ->and($graph->nodes)->toHaveCount(3)
            ->and($graph->edges)->toHaveCount(2);
    });

    it('rejects definitions without nodes', function (): void {
        expect(fn () => graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [],
            'edges' => [],
        ]))->toThrow(WorkflowValidationException::class);
    });
});

describe('WorkflowGraphValidator', function (): void {
    it('accepts a valid linear workflow graph', function (): void {
        workflowGraphValidator()->validate(graphFromDefinition(validWorkflowDefinition()));

        expect(true)->toBeTrue();
    });

    it('accepts a single-node workflow graph', function (): void {
        workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'only',
            'nodes' => [
                ['id' => 'only', 'type' => 'http', 'config' => []],
            ],
            'edges' => [],
        ]));

        expect(true)->toBeTrue();
    });

    it('accepts a branching graph with multiple terminal nodes', function (): void {
        workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'condition', 'config' => []],
                ['id' => 'yes', 'type' => 'http', 'config' => []],
                ['id' => 'no', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'yes', 'source_handle' => 'true'],
                ['id' => 'e2', 'source' => 'start', 'target' => 'no', 'source_handle' => 'false'],
            ],
        ]));

        expect(true)->toBeTrue();
    });

    it('rejects duplicate node ids', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'start', 'type' => 'delay', 'config' => []],
            ],
            'edges' => [],
        ])))->toThrow(InvalidWorkflowNodeException::class);
    });

    it('rejects unsupported node types', function (): void {
        expect(fn () => graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'unknown', 'config' => []],
            ],
            'edges' => [],
        ]))->toThrow(InvalidWorkflowNodeException::class);
    });

    it('rejects edges with unknown source nodes', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'missing', 'target' => 'finish'],
            ],
        ])))->toThrow(InvalidWorkflowEdgeException::class);
    });

    it('rejects edges with unknown target nodes', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'missing'],
            ],
        ])))->toThrow(InvalidWorkflowEdgeException::class);
    });

    it('rejects self-loop edges', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'start'],
            ],
        ])))->toThrow(InvalidWorkflowEdgeException::class);
    });

    it('rejects duplicate edge connections', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'finish'],
                ['id' => 'e2', 'source' => 'start', 'target' => 'finish'],
            ],
        ])))->toThrow(InvalidWorkflowEdgeException::class);
    });

    it('rejects duplicate edge ids', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'mid', 'type' => 'delay', 'config' => []],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'mid'],
                ['id' => 'e1', 'source' => 'mid', 'target' => 'finish'],
            ],
        ])))->toThrow(InvalidWorkflowEdgeException::class);
    });

    it('rejects graphs without a valid entry node id', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'missing',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
            ],
            'edges' => [],
        ])))->toThrow(MissingRootNodeException::class);
    });

    it('rejects entry nodes with incoming edges', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'finish', 'target' => 'start'],
            ],
        ])))->toThrow(MissingRootNodeException::class);
    });

    it('rejects graphs with multiple root candidates', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'orphan', 'type' => 'delay', 'config' => []],
            ],
            'edges' => [],
        ])))->toThrow(MissingRootNodeException::class);
    });

    it('rejects graphs without a terminal node', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'a', 'type' => 'delay', 'config' => []],
                ['id' => 'b', 'type' => 'script', 'config' => []],
                ['id' => 'c', 'type' => 'condition', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'a'],
                ['id' => 'e2', 'source' => 'a', 'target' => 'b'],
                ['id' => 'e3', 'source' => 'b', 'target' => 'c'],
                ['id' => 'e4', 'source' => 'c', 'target' => 'a'],
            ],
        ])))->toThrow(MissingTerminalNodeException::class);
    });

    it('detects cycles in the workflow graph', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'a', 'type' => 'delay', 'config' => []],
                ['id' => 'b', 'type' => 'condition', 'config' => []],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'a'],
                ['id' => 'e2', 'source' => 'a', 'target' => 'b'],
                ['id' => 'e3', 'source' => 'b', 'target' => 'a'],
                ['id' => 'e4', 'source' => 'b', 'target' => 'finish'],
            ],
        ])))->toThrow(CycleDetectedException::class);
    });

    it('includes the cycle path in cycle exceptions', function (): void {
        try {
            workflowGraphValidator()->validate(graphFromDefinition([
                'entry_node_id' => 'start',
                'nodes' => [
                    ['id' => 'start', 'type' => 'http', 'config' => []],
                    ['id' => 'a', 'type' => 'delay', 'config' => []],
                    ['id' => 'b', 'type' => 'condition', 'config' => []],
                    ['id' => 'finish', 'type' => 'script', 'config' => []],
                ],
                'edges' => [
                    ['id' => 'e1', 'source' => 'start', 'target' => 'a'],
                    ['id' => 'e2', 'source' => 'a', 'target' => 'b'],
                    ['id' => 'e3', 'source' => 'b', 'target' => 'a'],
                    ['id' => 'e4', 'source' => 'b', 'target' => 'finish'],
                ],
            ]));

            expect(false)->toBeTrue('Expected CycleDetectedException to be thrown.');
        } catch (CycleDetectedException $exception) {
            expect($exception->cyclePath)->toContain('a', 'b');
        }
    });

    it('rejects unreachable nodes', function (): void {
        expect(fn () => workflowGraphValidator()->validate(graphFromDefinition([
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'http', 'config' => []],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
                ['id' => 'island', 'type' => 'delay', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'finish'],
            ],
        ])))->toThrow(WorkflowValidationException::class);
    });

    it('reports disconnected islands as multiple root candidates', function (): void {
        try {
            workflowGraphValidator()->validate(graphFromDefinition([
                'entry_node_id' => 'start',
                'nodes' => [
                    ['id' => 'start', 'type' => 'http', 'config' => []],
                    ['id' => 'finish', 'type' => 'script', 'config' => []],
                    ['id' => 'island', 'type' => 'delay', 'config' => []],
                ],
                'edges' => [
                    ['id' => 'e1', 'source' => 'start', 'target' => 'finish'],
                ],
            ]));

            expect(false)->toBeTrue('Expected MissingRootNodeException to be thrown.');
        } catch (MissingRootNodeException $exception) {
            expect($exception->getMessage())->toContain('multiple root')
                ->and($exception->errors['entry_node_id'])->toContain('island');
        }
    });

    it('is bound in the service container', function (): void {
        expect(app(WorkflowGraphValidatorContract::class))
            ->toBeInstanceOf(WorkflowGraphValidator::class);
    });
});
