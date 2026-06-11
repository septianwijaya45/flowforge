import { useAuth } from '@/app/providers/auth-provider';
import { ActiveRunsList } from '@/modules/monitoring/components/active-runs-list';
import { useTenantRunsRealtime } from '@/modules/monitoring/hooks/use-run-realtime';
import { useWorkflowRuns } from '@/modules/monitoring/hooks/use-workflow-runs';
import { PageHeader } from '@/shared/components/page-header';

export function MonitoringDashboardPage() {
    const { isAuthenticated } = useAuth();
    useTenantRunsRealtime();

    const { data, isLoading, isError, error } = useWorkflowRuns(
        { active_only: true, per_page: 20 },
        { enabled: isAuthenticated },
    );

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Monitoring"
                description="Live workflow execution monitor — active runs update in realtime via Reverb."
            />

            {isError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {error.message}
                </p>
            ) : null}

            <section className="space-y-3">
                <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                    Running workflows
                </h2>
                <ActiveRunsList runs={data?.runs ?? []} isLoading={isLoading} />
            </section>
        </div>
    );
}
