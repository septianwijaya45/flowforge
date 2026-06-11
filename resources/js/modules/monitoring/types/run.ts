export type RunStatus =
    | 'pending'
    | 'running'
    | 'success'
    | 'failed'
    | 'cancelled'
    | 'timed_out';

export type StepStatus = 'pending' | 'running' | 'success' | 'failed' | 'cancelled';

export interface WorkflowRunStep {
    id: string;
    workflow_run_id: string;
    node_id: string;
    node_type: string;
    node_label: string | null;
    status: StepStatus;
    attempt: number;
    execution_order: number | null;
    duration_ms: number | null;
    error: Record<string, unknown> | null;
    started_at: string | null;
    completed_at: string | null;
}

export interface WorkflowRun {
    id: string;
    workflow_id: string;
    workflow_name?: string;
    workflow_version_id: string | null;
    status: RunStatus;
    trigger_type: string;
    input?: Record<string, unknown> | null;
    output?: Record<string, unknown> | null;
    error?: Record<string, unknown> | null;
    started_at: string | null;
    completed_at: string | null;
    created_at: string;
    steps?: WorkflowRunStep[];
}

export interface ListWorkflowRunsParams {
    page?: number;
    per_page?: number;
    status?: RunStatus;
    active_only?: boolean;
}

export interface WorkflowRunListResult {
    runs: WorkflowRun[];
    pagination: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
}
