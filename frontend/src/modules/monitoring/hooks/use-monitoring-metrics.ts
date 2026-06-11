import { useQuery } from '@tanstack/react-query';
import type { UseQueryOptions } from '@tanstack/react-query';

import { metricsApi } from '@/modules/monitoring/api/metrics-api';
import { monitoringKeys } from '@/modules/monitoring/query-keys';
import type { MonitoringMetrics } from '@/modules/monitoring/types/metrics';

type UseMonitoringMetricsOptions = Omit<
    UseQueryOptions<MonitoringMetrics, Error>,
    'queryKey' | 'queryFn'
>;

export function useMonitoringMetrics(days = 30, options?: UseMonitoringMetricsOptions) {
    return useQuery({
        queryKey: monitoringKeys.metrics(days),
        queryFn: async () => {
            const { data } = await metricsApi.get(days);

            return data.data.metrics;
        },
        ...options,
    });
}
