import { useState } from 'react';

import { useAuth } from '@/app/providers/auth-provider';
import { ActiveRunsList } from '@/modules/monitoring/components/active-runs-list';
import { ExecutionTrendChart } from '@/modules/monitoring/components/execution-trend-chart';
import { MetricsSummaryCards } from '@/modules/monitoring/components/metrics-summary-cards';
import { RunOutcomesChart } from '@/modules/monitoring/components/run-outcomes-chart';
import { RunsHistoryTable } from '@/modules/monitoring/components/runs-history-table';
import { useMonitoringMetrics } from '@/modules/monitoring/hooks/use-monitoring-metrics';
import { useTenantRunsRealtime } from '@/modules/monitoring/hooks/use-run-realtime';
import { useWorkflowRuns } from '@/modules/monitoring/hooks/use-workflow-runs';
import type { RunStatus } from '@/modules/monitoring/types/run';
import { PageHeader } from '@/shared/components/page-header';

const METRICS_DAYS = 30;
const RUNS_PER_PAGE = 15;

export function MonitoringDashboardPage() {
    const { apiAuthReady } = useAuth();
    const [page, setPage] = useState(1);
    const [status, setStatus] = useState<RunStatus | ''>('');

    useTenantRunsRealtime();

    const {
        data: metrics,
        isLoading: metricsLoading,
        isError: metricsError,
        error: metricsErrorMessage,
    } = useMonitoringMetrics(METRICS_DAYS, { enabled: apiAuthReady });

    const {
        data: activeRunsData,
        isLoading: activeRunsLoading,
        isError: activeRunsError,
        error: activeRunsErrorMessage,
    } = useWorkflowRuns({ active_only: true, per_page: 20 }, { enabled: apiAuthReady });

    const {
        data: historyData,
        isLoading: historyLoading,
        isError: historyError,
        error: historyErrorMessage,
    } = useWorkflowRuns(
        {
            page,
            per_page: RUNS_PER_PAGE,
            status: status || undefined,
        },
        { enabled: apiAuthReady },
    );

    const errorMessage = metricsError
        ? metricsErrorMessage.message
        : activeRunsError
          ? activeRunsErrorMessage.message
          : historyError
            ? historyErrorMessage.message
            : null;

    const handleStatusChange = (value: RunStatus | '') => {
        setStatus(value);
        setPage(1);
    };

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Monitoring"
                description="Detailed execution monitor with charts, live updates, and searchable run history."
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
                <ActiveRunsList runs={activeRunsData?.runs ?? []} isLoading={activeRunsLoading} />
            </section>

            <RunsHistoryTable
                runs={historyData?.runs ?? []}
                isLoading={historyLoading}
                page={historyData?.pagination.current_page ?? page}
                lastPage={historyData?.pagination.last_page ?? 1}
                total={historyData?.pagination.total ?? 0}
                perPage={historyData?.pagination.per_page ?? RUNS_PER_PAGE}
                status={status}
                onPageChange={setPage}
                onStatusChange={handleStatusChange}
            />
        </div>
    );
}
