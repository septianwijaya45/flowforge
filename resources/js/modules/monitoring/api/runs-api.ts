import { serviceClients } from '@/core/api/http-client';
import type { WorkflowRun } from '@/modules/monitoring/types/run';

interface RunsListResponse {
    runs: WorkflowRun[];
}

export const runsApi = {
    list: () => serviceClients.monitoring.get<RunsListResponse>('/monitoring/runs'),

    get: (id: string) =>
        serviceClients.monitoring.get<{ run: WorkflowRun }>(`/monitoring/runs/${id}`),
};
