import { serviceClients } from '@/core/api/http-client';
import type {
    ListWorkflowsParams,
    Workflow,
    WorkflowListResult,
} from '@/modules/workflow/types/workflow';

interface WorkflowListResponse {
    success: boolean;
    data: WorkflowListResult;
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

    update: (id: string, payload: Partial<Pick<Workflow, 'name' | 'description' | 'status'>>) =>
        serviceClients.workflow.put<WorkflowMutationResponse>(`/workflows/${id}`, payload),

    destroy: (id: string) => serviceClients.workflow.delete(`/workflows/${id}`),
};
