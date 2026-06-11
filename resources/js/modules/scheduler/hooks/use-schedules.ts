import { useQuery } from '@tanstack/react-query';

import { scheduleApi } from '@/modules/scheduler/api/schedule-api';
import { schedulerKeys } from '@/modules/scheduler/query-keys';

export function useSchedules() {
    return useQuery({
        queryKey: schedulerKeys.lists(),
        queryFn: async () => {
            const { data } = await scheduleApi.list();

            return data.schedules;
        },
        enabled: false,
    });
}
