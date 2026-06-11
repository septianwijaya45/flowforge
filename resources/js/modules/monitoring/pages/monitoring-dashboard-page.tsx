import { PageHeader } from '@/shared/components/page-header';

export function MonitoringDashboardPage() {
    return (
        <div className="flex flex-col gap-6 p-6">
            <PageHeader
                title="Monitoring"
                description="Track workflow runs, step logs, and execution health."
            />
        </div>
    );
}
