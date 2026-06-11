import { serviceClients } from '@/core/api/http-client';
import type { WorkflowRun } from '@/modules/workflow/types/workflow';

interface RunWorkflowResponse {
    success: boolean;
    message: string;
    data: {
        run: WorkflowRun;
    };
}

export const triggerApi = {
    runManual: (workflowId: string, input?: Record<string, unknown>) =>
        serviceClients.workflow.post<RunWorkflowResponse>(
            `/workflows/${workflowId}/trigger/manual`,
            { input: input ?? {} },
        ),
};
