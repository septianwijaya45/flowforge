import { useQuery } from '@tanstack/react-query';
import type { UseQueryOptions } from '@tanstack/react-query';

import { runsApi } from '@/modules/monitoring/api/runs-api';
import { monitoringKeys } from '@/modules/monitoring/query-keys';
import type { ListWorkflowRunsParams, WorkflowRunListResult } from '@/modules/monitoring/types/run';

type UseWorkflowRunsOptions = Omit<
    UseQueryOptions<WorkflowRunListResult, Error>,
    'queryKey' | 'queryFn'
>;

export function useWorkflowRuns(
    params: ListWorkflowRunsParams = {},
    options?: UseWorkflowRunsOptions,
) {
    return useQuery({
        queryKey: monitoringKeys.list(params),
        queryFn: async () => {
            const { data } = await runsApi.list(params);

            return data.data;
        },
        ...options,
    });
}
