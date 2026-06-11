import { useQuery } from '@tanstack/react-query';

import { ApiError } from '@/core/api/types/api-error';
import { versionApi } from '@/modules/workflow/api/version-api';
import { workflowKeys } from '@/modules/workflow/query-keys';
import { createDefaultDefinition } from '@/modules/workflow/utils/graph-serializer';

export function useWorkflowBuilder(workflowId: string) {
    const workflowQuery = useQuery({
        queryKey: workflowKeys.detail(workflowId),
        queryFn: async () => {
            const { data } = await versionApi.getWorkflow(workflowId);

            return data.data.workflow;
        },
    });

    const versionQuery = useQuery({
        queryKey: [...workflowKeys.detail(workflowId), 'current-version'] as const,
        queryFn: async () => {
            try {
                const { data } = await versionApi.getCurrentVersion(workflowId);

                return data.data.version;
            } catch (error) {
                if (isNotFoundError(error)) {
                    return null;
                }

                throw error;
            }
        },
    });

    const initialDefinition =
        versionQuery.data?.definition ?? (versionQuery.isSuccess ? createDefaultDefinition() : null);

    return {
        workflow: workflowQuery.data,
        version: versionQuery.data,
        initialDefinition,
        isLoading: workflowQuery.isLoading || versionQuery.isLoading,
        isError: workflowQuery.isError || versionQuery.isError,
        error: workflowQuery.error ?? versionQuery.error,
    };
}

function isNotFoundError(error: unknown): boolean {
    return error instanceof ApiError && error.status === 404;
}
