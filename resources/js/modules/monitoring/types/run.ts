export type RunStatus = 'pending' | 'running' | 'completed' | 'failed' | 'cancelled';

export interface WorkflowRun {
    id: string;
    workflow_id: number;
    status: RunStatus;
    started_at: string | null;
    finished_at: string | null;
}
