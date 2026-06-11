import { useQuery } from '@tanstack/react-query';
import type { UseQueryOptions } from '@tanstack/react-query';

import { runsApi } from '@/modules/monitoring/api/runs-api';
import { monitoringKeys } from '@/modules/monitoring/query-keys';
import type { WorkflowRun } from '@/modules/monitoring/types/run';

type UseRunDetailOptions = Omit<UseQueryOptions<WorkflowRun, Error>, 'queryKey' | 'queryFn'>;

export function useRunDetail(runId: string, options?: UseRunDetailOptions) {
    return useQuery({
        queryKey: monitoringKeys.detail(runId),
        queryFn: async () => {
            const { data } = await runsApi.get(runId);

            return data.data.run;
        },
        ...options,
    });
}
