import { useMutation, useQueryClient } from '@tanstack/react-query';

import { versionApi } from '@/modules/workflow/api/version-api';
import { workflowKeys } from '@/modules/workflow/query-keys';
import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';

interface SaveWorkflowVersionInput {
    workflowId: string;
    definition: WorkflowDefinition;
    changeSummary?: string;
}

export function useSaveWorkflowVersion() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({ workflowId, definition, changeSummary }: SaveWorkflowVersionInput) => {
            const { data } = await versionApi.saveVersion(workflowId, definition, changeSummary);

            return data.data.version;
        },
        onSuccess: (_version, variables) => {
            queryClient.invalidateQueries({ queryKey: workflowKeys.detail(variables.workflowId) });
            queryClient.invalidateQueries({
                queryKey: [...workflowKeys.detail(variables.workflowId), 'current-version'],
            });
            queryClient.invalidateQueries({ queryKey: workflowKeys.lists() });
        },
    });
}
