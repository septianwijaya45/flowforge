import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { UseQueryOptions } from '@tanstack/react-query';

import { triggerApi } from '@/modules/workflow/api/trigger-api';
import { triggerKeys } from '@/modules/workflow/query-keys';
import { schedulerKeys } from '@/modules/scheduler/query-keys';
import type {
    CreateTriggerInput,
    UpdateTriggerInput,
    WorkflowTrigger,
} from '@/modules/workflow/types/trigger';

export function useWorkflowTriggers(
    workflowId: string,
    options?: Omit<UseQueryOptions<WorkflowTrigger[], Error>, 'queryKey' | 'queryFn'>,
) {
    return useQuery({
        queryKey: triggerKeys.list(workflowId),
        queryFn: async () => {
            const { data } = await triggerApi.list(workflowId);

            return data.data.triggers;
        },
        ...options,
    });
}

export function useCreateTrigger(workflowId: string) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (input: CreateTriggerInput) => {
            const { data } = await triggerApi.create(workflowId, input);

            return data.data.trigger;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: triggerKeys.list(workflowId) });
            queryClient.invalidateQueries({ queryKey: schedulerKeys.lists() });
        },
    });
}

export function useUpdateTrigger(workflowId: string) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({
            triggerId,
            input,
        }: {
            triggerId: string;
            input: UpdateTriggerInput;
        }) => {
            const { data } = await triggerApi.update(workflowId, triggerId, input);

            return data.data.trigger;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: triggerKeys.list(workflowId) });
            queryClient.invalidateQueries({ queryKey: schedulerKeys.lists() });
        },
    });
}

export function useDeleteTrigger(workflowId: string) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (triggerId: string) => {
            await triggerApi.destroy(workflowId, triggerId);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: triggerKeys.list(workflowId) });
            queryClient.invalidateQueries({ queryKey: schedulerKeys.lists() });
        },
    });
}
