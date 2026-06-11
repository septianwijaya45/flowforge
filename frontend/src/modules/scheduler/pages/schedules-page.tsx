import { useAuth } from '@/app/providers/auth-provider';
import { canWrite } from '@/core/auth/permissions';
import { CreateScheduleDialog } from '@/modules/scheduler/components/create-schedule-dialog';
import { ScheduleList } from '@/modules/scheduler/components/schedule-list';
import { useSchedules } from '@/modules/scheduler/hooks/use-schedules';
import { useWorkflows } from '@/modules/workflow/hooks/use-workflows';
import { PageHeader } from '@/shared/components/page-header';

export function SchedulesPage() {
    const { apiAuthReady, user } = useAuth();
    const userCanWrite = canWrite(user?.role as string | undefined);

    const { data: schedules, isLoading, isError, error } = useSchedules({
        enabled: apiAuthReady,
    });

    const { data: workflowData } = useWorkflows(
        { page: 1, per_page: 100, status: 'active' },
        { enabled: apiAuthReady && userCanWrite },
    );

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Schedules"
                description="Cron-based triggers across all workflows in this tenant."
                actions={
                    userCanWrite ? (
                        <CreateScheduleDialog workflows={workflowData?.workflows ?? []} />
                    ) : undefined
                }
            />

            {isError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {error.message}
                </p>
            ) : null}

            <ScheduleList
                schedules={schedules ?? []}
                isLoading={isLoading}
                canWrite={userCanWrite}
            />
        </div>
    );
}
