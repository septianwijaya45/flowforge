import { useAuth } from '@/app/providers/auth-provider';
import { ActiveRunsList } from '@/modules/monitoring/components/active-runs-list';
import { ExecutionTrendChart } from '@/modules/monitoring/components/execution-trend-chart';
import { MetricsSummaryCards } from '@/modules/monitoring/components/metrics-summary-cards';
import { RunOutcomesChart } from '@/modules/monitoring/components/run-outcomes-chart';
import { useMonitoringMetrics } from '@/modules/monitoring/hooks/use-monitoring-metrics';
import { useTenantRunsRealtime } from '@/modules/monitoring/hooks/use-run-realtime';
import { useWorkflowRuns } from '@/modules/monitoring/hooks/use-workflow-runs';
import { PageHeader } from '@/shared/components/page-header';

const METRICS_DAYS = 30;

export function MonitoringDashboardPage() {
    const { isAuthenticated } = useAuth();
    useTenantRunsRealtime();

    const {
        data: metrics,
        isLoading: metricsLoading,
        isError: metricsError,
        error: metricsErrorMessage,
    } = useMonitoringMetrics(METRICS_DAYS, { enabled: isAuthenticated });

    const {
        data: activeRunsData,
        isLoading: runsLoading,
        isError: runsError,
        error: runsErrorMessage,
    } = useWorkflowRuns({ active_only: true, per_page: 20 }, { enabled: isAuthenticated });

    const errorMessage = metricsError
        ? metricsErrorMessage.message
        : runsError
          ? runsErrorMessage.message
          : null;

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Monitoring"
                description="Workflow health overview with live active runs and execution metrics."
            />

            {errorMessage ? (
                <p className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {errorMessage}
                </p>
            ) : null}

            <MetricsSummaryCards metrics={metrics} isLoading={metricsLoading} />

            <div className="grid gap-4 lg:grid-cols-2">
                <RunOutcomesChart metrics={metrics} isLoading={metricsLoading} />
                <ExecutionTrendChart metrics={metrics} isLoading={metricsLoading} />
            </div>

            <section className="space-y-3">
                <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                    Active runs
                </h2>
                <ActiveRunsList runs={activeRunsData?.runs ?? []} isLoading={runsLoading} />
            </section>
        </div>
    );
}
