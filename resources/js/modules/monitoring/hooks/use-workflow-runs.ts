import { useQuery } from '@tanstack/react-query';

import { runsApi } from '@/modules/monitoring/api/runs-api';
import { monitoringKeys } from '@/modules/monitoring/query-keys';

export function useWorkflowRuns() {
    return useQuery({
        queryKey: monitoringKeys.lists(),
        queryFn: async () => {
            const { data } = await runsApi.list();

            return data.runs;
        },
        enabled: false,
    });
}
