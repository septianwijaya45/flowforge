import { serviceClients } from '@/core/api/http-client';
import type {
    CreateScheduleInput,
    Schedule,
    UpdateScheduleInput,
} from '@/modules/scheduler/types/schedule';

interface SchedulesListResponse {
    success: boolean;
    data: {
        schedules: Schedule[];
    };
}

interface ScheduleMutationResponse {
    success: boolean;
    data: {
        schedule: Schedule;
    };
}

export const scheduleApi = {
    list: () => serviceClients.scheduler.get<SchedulesListResponse>('/schedules'),

    get: (id: string) =>
        serviceClients.scheduler.get<{ success: boolean; data: { schedule: Schedule } }>(
            `/schedules/${id}`,
        ),

    create: (payload: CreateScheduleInput) =>
        serviceClients.scheduler.post<ScheduleMutationResponse>('/schedules', payload),

    update: (id: string, payload: UpdateScheduleInput) =>
        serviceClients.scheduler.put<ScheduleMutationResponse>(`/schedules/${id}`, payload),

    destroy: (id: string) => serviceClients.scheduler.delete(`/schedules/${id}`),
};
