import { serviceClients } from '@/core/api/http-client';
import type { Schedule } from '@/modules/scheduler/types/schedule';

interface SchedulesListResponse {
    schedules: Schedule[];
}

export const scheduleApi = {
    list: () => serviceClients.scheduler.get<SchedulesListResponse>('/schedules'),

    get: (id: string) =>
        serviceClients.scheduler.get<{ schedule: Schedule }>(`/schedules/${id}`),
};
