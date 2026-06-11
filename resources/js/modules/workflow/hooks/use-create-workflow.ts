import { useMutation, useQueryClient } from '@tanstack/react-query';

import { workflowApi } from '@/modules/workflow/api/workflow-api';
import { workflowKeys } from '@/modules/workflow/query-keys';
import type { Workflow } from '@/modules/workflow/types/workflow';

interface CreateWorkflowInput {
    name: string;
    description?: string;
    slug?: string;
}

export function useCreateWorkflow() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (input: CreateWorkflowInput) => {
            const { data } = await workflowApi.create(input);

            return data.data.workflow;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: workflowKeys.lists() });
        },
    });
}
