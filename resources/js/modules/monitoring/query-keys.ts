import { createQueryKeys } from '@/core/query/query-keys';

export const monitoringKeys = {
    ...createQueryKeys('monitoring'),
    metrics: (days: number) => ['monitoring', 'metrics', days] as const,
};
