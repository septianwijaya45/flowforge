import { serviceClients } from '@/core/api/http-client';
import type { WorkflowRun } from '@/modules/workflow/types/workflow';
import type {
    CreateTriggerInput,
    UpdateTriggerInput,
    WorkflowTrigger,
} from '@/modules/workflow/types/trigger';

interface TriggersListResponse {
    success: boolean;
    data: {
        triggers: WorkflowTrigger[];
    };
}

interface TriggerMutationResponse {
    success: boolean;
    data: {
        trigger: WorkflowTrigger;
    };
}

interface RunWorkflowResponse {
    success: boolean;
    message: string;
    data: {
        run: WorkflowRun;
    };
}

export const triggerApi = {
    list: (workflowId: string) =>
        serviceClients.workflow.get<TriggersListResponse>(`/workflows/${workflowId}/triggers`),

    create: (workflowId: string, payload: CreateTriggerInput) =>
        serviceClients.workflow.post<TriggerMutationResponse>(
            `/workflows/${workflowId}/triggers`,
            payload,
        ),

    update: (workflowId: string, triggerId: string, payload: UpdateTriggerInput) =>
        serviceClients.workflow.put<TriggerMutationResponse>(
            `/workflows/${workflowId}/triggers/${triggerId}`,
            payload,
        ),

    destroy: (workflowId: string, triggerId: string) =>
        serviceClients.workflow.delete(`/workflows/${workflowId}/triggers/${triggerId}`),

    runManual: (workflowId: string, input?: Record<string, unknown>) =>
        serviceClients.workflow.post<RunWorkflowResponse>(
            `/workflows/${workflowId}/trigger/manual`,
            { input: input ?? {} },
        ),
};
