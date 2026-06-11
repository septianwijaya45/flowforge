import { Link } from '@inertiajs/react';
import { ArrowLeft, Radio } from 'lucide-react';

import { useAuth } from '@/app/providers/auth-provider';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { appRoutes } from '@/core/constants/routes';
import { getEcho } from '@/core/realtime/echo-client';
import { RunStatusBadge } from '@/modules/monitoring/components/run-status-badge';
import { RunStepTimeline } from '@/modules/monitoring/components/run-step-timeline';
import { useRunDetail } from '@/modules/monitoring/hooks/use-run-detail';
import { useRunRealtime } from '@/modules/monitoring/hooks/use-run-realtime';

interface RunDetailPageProps {
    runId: string;
}

export function RunDetailPage({ runId }: RunDetailPageProps) {
    const { apiAuthReady } = useAuth();
    const echoEnabled = getEcho() !== null;

    const { data: run, isLoading, isError, error } = useRunDetail(runId, {
        enabled: apiAuthReady,
    });

    useRunRealtime(runId);

    if (isLoading) {
        return (
            <div className="flex flex-col gap-4 p-6">
                <Skeleton className="h-10 w-72" />
                <Skeleton className="h-48 w-full" />
            </div>
        );
    }

    if (isError || !run) {
        return (
            <p className="m-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                {error?.message ?? 'Workflow run not found.'}
            </p>
        );
    }

    const isLive = run.status === 'pending' || run.status === 'running';

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div className="space-y-2">
                    <Button variant="ghost" size="sm" asChild className="-ml-2 w-fit">
                        <Link href={appRoutes.monitoring.dashboard}>
                            <ArrowLeft className="size-4" />
                            Monitoring
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-xl font-semibold">
                            {run.workflow_name ?? `Workflow ${run.workflow_id}`}
                        </h1>
                        <p className="text-sm text-muted-foreground">Run {run.id}</p>
                    </div>
                </div>

                <div className="flex flex-wrap items-center gap-3">
                    {echoEnabled && isLive ? (
                        <span className="inline-flex items-center gap-1.5 text-xs text-blue-600">
                            <Radio className="size-3.5" />
                            Live
                        </span>
                    ) : null}
                    <RunStatusBadge status={run.status} pulse={run.status === 'running'} />
                </div>
            </div>

            <div className="grid gap-4 text-sm sm:grid-cols-3">
                <Metric label="Trigger" value={run.trigger_type} />
                <Metric
                    label="Started"
                    value={run.started_at ? new Date(run.started_at).toLocaleString() : '—'}
                />
                <Metric
                    label="Completed"
                    value={run.completed_at ? new Date(run.completed_at).toLocaleString() : '—'}
                />
            </div>

            {run.error ? (
                <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {String(run.error.message ?? 'Workflow run failed')}
                </div>
            ) : null}

            <section className="space-y-3">
                <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                    Step timeline
                </h2>
                <RunStepTimeline steps={run.steps ?? []} />
            </section>
        </div>
    );
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg border p-3">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className="mt-1 font-medium capitalize">{value}</p>
        </div>
    );
}
