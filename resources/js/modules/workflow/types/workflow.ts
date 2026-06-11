import type { PaginationMeta } from '@/core/api/types/api-response';

export type WorkflowStatus = 'draft' | 'active' | 'archived' | 'disabled';

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
    status?: WorkflowStatus | '';
    sort?: 'name' | 'slug' | 'created_at' | 'updated_at' | 'status';
    direction?: 'asc' | 'desc';
}

export interface WorkflowListResult {
    workflows: Workflow[];
    pagination: PaginationMeta;
}

export interface WorkflowRun {
    id: string;
    workflow_id: number;
    workflow_version_id: number | null;
    status: string;
    trigger_type: string;
    created_at: string;
}
