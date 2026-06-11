import { useQuery  } from '@tanstack/react-query';
import type {UseQueryOptions} from '@tanstack/react-query';

import { workflowApi } from '@/modules/workflow/api/workflow-api';
import { workflowKeys } from '@/modules/workflow/query-keys';
import type { ListWorkflowsParams, Workflow } from '@/modules/workflow/types/workflow';

type UseWorkflowsOptions = Omit<
    UseQueryOptions<Workflow[], Error>,
    'queryKey' | 'queryFn'
>;

export function useWorkflows(params?: ListWorkflowsParams, options?: UseWorkflowsOptions) {
    return useQuery({
        queryKey: workflowKeys.list(params ?? {}),
        queryFn: async () => {
            const { data } = await workflowApi.list(params);

            return data.data.workflows;
        },
        ...options,
    });
}
