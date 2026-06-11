import { useQuery } from '@tanstack/react-query';
import type { UseQueryOptions } from '@tanstack/react-query';

import { workflowApi } from '@/modules/workflow/api/workflow-api';
import { workflowKeys } from '@/modules/workflow/query-keys';
import type { ListWorkflowsParams, WorkflowListResult } from '@/modules/workflow/types/workflow';

type UseWorkflowsOptions = Omit<
    UseQueryOptions<WorkflowListResult, Error>,
    'queryKey' | 'queryFn'
>;

function toQueryParams(params: ListWorkflowsParams): ListWorkflowsParams {
    const cleaned: ListWorkflowsParams = {
        page: params.page ?? 1,
        per_page: params.per_page ?? 15,
        sort: params.sort ?? 'created_at',
        direction: params.direction ?? 'desc',
    };

    if (params.search?.trim()) {
        cleaned.search = params.search.trim();
    }

    if (params.status) {
        cleaned.status = params.status;
    }

    return cleaned;
}

export function useWorkflows(params: ListWorkflowsParams = {}, options?: UseWorkflowsOptions) {
    const queryParams = toQueryParams(params);

    return useQuery({
        queryKey: workflowKeys.list(queryParams),
        queryFn: async () => {
            const { data } = await workflowApi.list(queryParams);

            return data.data;
        },
        placeholderData: (previous) => previous,
        ...options,
    });
}
