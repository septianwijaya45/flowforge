export type WorkflowNodeType =
    | 'http'
    | 'delay'
    | 'condition'
    | 'script'
    | 'email'
    | 'database'
    | 'webhook';

export interface WorkflowNodePosition {
    x: number;
    y: number;
}

export interface WorkflowGraphNode {
    id: string;
    type: WorkflowNodeType;
    config: Record<string, unknown>;
    position?: WorkflowNodePosition;
}

export interface WorkflowGraphEdge {
    id: string;
    source: string;
    target: string;
    source_handle?: string | null;
}

export interface WorkflowDefinition {
    entry_node_id: string;
    nodes: WorkflowGraphNode[];
    edges: WorkflowGraphEdge[];
}

export interface WorkflowVersion {
    id: string;
    workflow_id: string;
    version_number: number;
    definition: WorkflowDefinition;
    definition_hash: string | null;
    change_summary: string | null;
    is_current: boolean;
    created_at: string;
    updated_at: string;
}
