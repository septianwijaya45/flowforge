import { serviceClients } from '@/core/api/http-client';
import type { Workflow, ListWorkflowsParams } from '@/modules/workflow/types/workflow';

interface WorkflowListResponse {
    success: boolean;
    data: {
        workflows: Workflow[];
        pagination: {
            current_page: number;
            per_page: number;
            total: number;
            last_page: number;
        };
    };
}

interface WorkflowMutationResponse {
    success: boolean;
    data: {
        workflow: Workflow;
    };
}

export const workflowApi = {
    list: (params?: ListWorkflowsParams) =>
        serviceClients.workflow.get<WorkflowListResponse>('/workflows', { params }),

    create: (payload: Pick<Workflow, 'name' | 'description'> & { slug?: string }) =>
        serviceClients.workflow.post<WorkflowMutationResponse>('/workflows', payload),

    update: (id: number, payload: Partial<Pick<Workflow, 'name' | 'description' | 'status'>>) =>
        serviceClients.workflow.put<WorkflowMutationResponse>(`/workflows/${id}`, payload),

    destroy: (id: number) => serviceClients.workflow.delete(`/workflows/${id}`),
};
