import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { UseQueryOptions } from '@tanstack/react-query';

import { scheduleApi } from '@/modules/scheduler/api/schedule-api';
import { schedulerKeys } from '@/modules/scheduler/query-keys';
import { triggerKeys } from '@/modules/workflow/query-keys';
import type {
    CreateScheduleInput,
    Schedule,
    UpdateScheduleInput,
} from '@/modules/scheduler/types/schedule';

export function useSchedules(
    options?: Omit<UseQueryOptions<Schedule[], Error>, 'queryKey' | 'queryFn'>,
) {
    return useQuery({
        queryKey: schedulerKeys.lists(),
        queryFn: async () => {
            const { data } = await scheduleApi.list();

            return data.data.schedules;
        },
        ...options,
    });
}

export function useCreateSchedule() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (input: CreateScheduleInput) => {
            const { data } = await scheduleApi.create(input);

            return data.data.schedule;
        },
        onSuccess: (schedule) => {
            queryClient.invalidateQueries({ queryKey: schedulerKeys.lists() });
            queryClient.invalidateQueries({ queryKey: triggerKeys.list(schedule.workflow_id) });
        },
    });
}

export function useUpdateSchedule() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({
            scheduleId,
            input,
        }: {
            scheduleId: string;
            input: UpdateScheduleInput;
        }) => {
            const { data } = await scheduleApi.update(scheduleId, input);

            return data.data.schedule;
        },
        onSuccess: (schedule) => {
            queryClient.invalidateQueries({ queryKey: schedulerKeys.lists() });
            queryClient.invalidateQueries({ queryKey: triggerKeys.list(schedule.workflow_id) });
        },
    });
}

export function useDeleteSchedule() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (scheduleId: string) => {
            await scheduleApi.destroy(scheduleId);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: schedulerKeys.lists() });
            queryClient.invalidateQueries({ queryKey: triggerKeys.all });
        },
    });
}
