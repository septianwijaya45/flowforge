import { serviceClients } from '@/core/api/http-client';
import type { Workflow } from '@/modules/workflow/types/workflow';
import type { WorkflowDefinition, WorkflowVersion } from '@/modules/workflow/types/workflow-graph';

interface WorkflowResponse {
    success: boolean;
    data: { workflow: Workflow };
}

interface VersionResponse {
    success: boolean;
    data: { version: WorkflowVersion };
}

export const versionApi = {
    getWorkflow: (workflowId: string) =>
        serviceClients.workflow.get<WorkflowResponse>(`/workflows/${workflowId}`),

    getCurrentVersion: (workflowId: string) =>
        serviceClients.workflow.get<VersionResponse>(`/workflows/${workflowId}/versions/current`),

    saveVersion: (
        workflowId: string,
        definition: WorkflowDefinition,
        changeSummary?: string,
    ) =>
        serviceClients.workflow.post<VersionResponse>(`/workflows/${workflowId}/versions`, {
            definition,
            change_summary: changeSummary,
        }),
};
