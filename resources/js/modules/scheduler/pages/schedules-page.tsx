import { PageHeader } from '@/shared/components/page-header';

export function SchedulesPage() {
    return (
        <div className="flex flex-col gap-6 p-6">
            <PageHeader
                title="Schedules"
                description="Manage cron-based workflow triggers."
            />
        </div>
    );
}
