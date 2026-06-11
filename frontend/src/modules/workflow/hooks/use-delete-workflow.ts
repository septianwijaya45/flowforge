import { useMutation, useQueryClient } from '@tanstack/react-query';

import { workflowApi } from '@/modules/workflow/api/workflow-api';
import { workflowKeys } from '@/modules/workflow/query-keys';

export function useDeleteWorkflow() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (workflowId: string) => {
            await workflowApi.destroy(workflowId);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: workflowKeys.lists() });
        },
    });
}
