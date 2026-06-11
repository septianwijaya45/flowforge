import { Link } from 'react-router-dom';

import { useAuth } from '@/app/providers/auth-provider';
import { appRoutes } from '@/core/constants/routes';
import { ActiveRunsList } from '@/modules/monitoring/components/active-runs-list';
import { MetricsSummaryCards } from '@/modules/monitoring/components/metrics-summary-cards';
import { QuickLinksGrid } from '@/modules/monitoring/components/quick-links-grid';
import { useMonitoringMetrics } from '@/modules/monitoring/hooks/use-monitoring-metrics';
import { useWorkflowRuns } from '@/modules/monitoring/hooks/use-workflow-runs';
import { PageHeader } from '@/shared/components/page-header';

const METRICS_DAYS = 7;
const RECENT_RUNS_LIMIT = 5;

export function DashboardHomePage() {
    const { apiAuthReady } = useAuth();

    const {
        data: metrics,
        isLoading: metricsLoading,
        isError: metricsError,
        error: metricsErrorMessage,
    } = useMonitoringMetrics(METRICS_DAYS, { enabled: apiAuthReady });

    const {
        data: recentRunsData,
        isLoading: runsLoading,
        isError: runsError,
        error: runsErrorMessage,
    } = useWorkflowRuns({ per_page: RECENT_RUNS_LIMIT }, { enabled: apiAuthReady });

    const errorMessage = metricsError
        ? metricsErrorMessage.message
        : runsError
          ? runsErrorMessage.message
          : null;

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Dashboard"
                description="High-level overview of your tenant. Jump into workflows or open monitoring for deeper execution insights."
            />

            {errorMessage ? (
                <p className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {errorMessage}
                </p>
            ) : null}

            <MetricsSummaryCards metrics={metrics} isLoading={metricsLoading} />

            <QuickLinksGrid />

            <section className="space-y-3">
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                        Recent runs
                    </h2>
                    <Link
                        to={appRoutes.monitoring.dashboard}
                        className="text-sm text-primary hover:underline"
                    >
                        View all in Monitoring
                    </Link>
                </div>
                <ActiveRunsList runs={recentRunsData?.runs ?? []} isLoading={runsLoading} />
            </section>
        </div>
    );
}
