import { serviceClients } from '@/core/api/http-client';
import type {
    ListWorkflowRunsParams,
    WorkflowRun,
    WorkflowRunListResult,
} from '@/modules/monitoring/types/run';

interface RunsListResponse {
    success: boolean;
    data: WorkflowRunListResult;
}

interface RunDetailResponse {
    success: boolean;
    data: {
        run: WorkflowRun;
    };
}

export const runsApi = {
    list: (params?: ListWorkflowRunsParams) =>
        serviceClients.monitoring.get<RunsListResponse>('/monitoring/runs', { params }),

    get: (id: string) => serviceClients.monitoring.get<RunDetailResponse>(`/monitoring/runs/${id}`),
};
