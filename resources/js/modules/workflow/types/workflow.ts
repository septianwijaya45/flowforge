export type WorkflowStatus = 'draft' | 'active' | 'archived';

export interface Workflow {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    status: WorkflowStatus;
    current_version_id: number | null;
    created_at: string;
    updated_at: string;
}

export interface ListWorkflowsParams {
    page?: number;
    per_page?: number;
    search?: string;
}
