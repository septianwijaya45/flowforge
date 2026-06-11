import type { Connection, Edge, Node } from '@xyflow/react';

import type {
    WorkflowDefinition,
    WorkflowGraphEdge,
    WorkflowGraphNode,
    WorkflowNodeType,
} from '@/modules/workflow/types/workflow-graph';

export interface WorkflowNodeData extends Record<string, unknown> {
    nodeType: WorkflowNodeType;
    label: string;
    config: Record<string, unknown>;
}

export type BuilderNode = Node<WorkflowNodeData, 'workflow'>;
export type BuilderEdge = Edge;

export class GraphSerializationError extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'GraphSerializationError';
    }
}

export function createNodeId(): string {
    return crypto.randomUUID();
}

export function createEdgeId(source: string, target: string, sourceHandle?: string | null): string {
    return `${source}-${sourceHandle ?? 'default'}-${target}`;
}

export function createDefaultDefinition(): WorkflowDefinition {
    const nodeId = createNodeId();

    return {
        entry_node_id: nodeId,
        nodes: [
            {
                id: nodeId,
                type: 'http',
                config: {
                    label: 'Start',
                    url: 'https://example.com',
                    method: 'GET',
                    timeout: 30,
                },
                position: { x: 120, y: 120 },
            },
        ],
        edges: [],
    };
}

export function definitionToFlow(definition: WorkflowDefinition): {
    nodes: BuilderNode[];
    edges: BuilderEdge[];
} {
    const nodes: BuilderNode[] = definition.nodes.map((node) => ({
        id: node.id,
        type: 'workflow',
        position: node.position ?? { x: 0, y: 0 },
        data: {
            nodeType: node.type,
            label: String(node.config.label ?? nodeTypeLabel(node.type)),
            config: node.config,
        },
    }));

    const edges: BuilderEdge[] = definition.edges.map((edge) => ({
        id: edge.id,
        source: edge.source,
        target: edge.target,
        sourceHandle: edge.source_handle ?? undefined,
    }));

    return { nodes, edges };
}

export function flowToDefinition(nodes: BuilderNode[], edges: BuilderEdge[]): WorkflowDefinition {
    if (nodes.length === 0) {
        throw new GraphSerializationError('Workflow must contain at least one node.');
    }

    const graphNodes: WorkflowGraphNode[] = nodes.map((node) => ({
        id: node.id,
        type: node.data.nodeType,
        config: {
            ...node.data.config,
            label: node.data.label,
        },
        position: {
            x: Math.round(node.position.x),
            y: Math.round(node.position.y),
        },
    }));

    const graphEdges: WorkflowGraphEdge[] = edges.map((edge) => ({
        id: edge.id,
        source: edge.source,
        target: edge.target,
        source_handle: edge.sourceHandle ?? null,
    }));

    const entryNodeId = resolveEntryNodeId(graphNodes, graphEdges);

    return {
        entry_node_id: entryNodeId,
        nodes: graphNodes,
        edges: graphEdges,
    };
}

export function createBuilderNode(
    type: WorkflowNodeType,
    position: { x: number; y: number },
    defaultConfig: Record<string, unknown>,
): BuilderNode {
    const id = createNodeId();

    return {
        id,
        type: 'workflow',
        position,
        data: {
            nodeType: type,
            label: String(defaultConfig.label ?? type),
            config: { ...defaultConfig },
        },
    };
}

export function isValidConnection(
    connection: Connection | { source: string; target: string; sourceHandle?: string | null },
    nodes: BuilderNode[],
): boolean {
    if (!connection.source || !connection.target || connection.source === connection.target) {
        return false;
    }

    const sourceNode = nodes.find((node) => node.id === connection.source);

    if (sourceNode?.data.nodeType === 'condition' && !connection.sourceHandle) {
        return false;
    }

    return true;
}

function resolveEntryNodeId(nodes: WorkflowGraphNode[], edges: WorkflowGraphEdge[]): string {
    const incomingTargets = new Set(edges.map((edge) => edge.target));
    const roots = nodes.filter((node) => !incomingTargets.has(node.id));

    if (roots.length === 0) {
        throw new GraphSerializationError('Workflow must have an entry node with no incoming edges.');
    }

    if (roots.length > 1) {
        throw new GraphSerializationError('Workflow must have exactly one entry node.');
    }

    return roots[0].id;
}

function nodeTypeLabel(type: WorkflowNodeType): string {
    const labels: Record<WorkflowNodeType, string> = {
        http: 'HTTP Request',
        delay: 'Delay',
        condition: 'Condition',
        script: 'Script',
        email: 'Send Email',
        database: 'Database Query',
        webhook: 'Webhook',
    };

    return labels[type];
}
