import { serviceClients } from '@/core/api/http-client';
import type { MonitoringMetrics } from '@/modules/monitoring/types/metrics';

interface MetricsResponse {
    success: boolean;
    data: {
        metrics: MonitoringMetrics;
    };
}

export const metricsApi = {
    get: (days = 30) =>
        serviceClients.monitoring.get<MetricsResponse>('/monitoring/metrics', {
            params: { days },
        }),
};
