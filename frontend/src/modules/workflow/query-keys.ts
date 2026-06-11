import { createQueryKeys } from '@/core/query/query-keys';

export const workflowKeys = createQueryKeys('workflow');

export const triggerKeys = {
    all: ['triggers'] as const,
    list: (workflowId: string) => [...triggerKeys.all, workflowId] as const,
};
