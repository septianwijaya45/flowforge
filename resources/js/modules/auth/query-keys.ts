import { createQueryKeys } from '@/core/query/query-keys';

export const authKeys = {
    ...createQueryKeys('auth'),
    session: () => ['auth', 'session'] as const,
};
