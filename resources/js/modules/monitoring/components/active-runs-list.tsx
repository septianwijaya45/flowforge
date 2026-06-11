import { Link } from '@inertiajs/react';

import { Skeleton } from '@/components/ui/skeleton';
import { appRoutes } from '@/core/constants/routes';
import { RunStatusBadge } from '@/modules/monitoring/components/run-status-badge';
import type { WorkflowRun } from '@/modules/monitoring/types/run';

interface ActiveRunsListProps {
    runs: WorkflowRun[];
    isLoading?: boolean;
}

export function ActiveRunsList({ runs, isLoading = false }: ActiveRunsListProps) {
    if (isLoading) {
        return (
            <div className="space-y-3">
                {Array.from({ length: 3 }).map((_, index) => (
                    <Skeleton key={index} className="h-16 w-full" />
                ))}
            </div>
        );
    }

    if (runs.length === 0) {
        return (
            <p className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                No active workflow runs right now.
            </p>
        );
    }

    return (
        <ul className="divide-y rounded-lg border">
            {runs.map((run) => (
                <li key={run.id}>
                    <Link
                        href={appRoutes.monitoring.runDetail(run.id)}
                        className="flex items-center justify-between gap-4 p-4 transition hover:bg-muted/40"
                    >
                        <div className="min-w-0">
                            <p className="truncate font-medium">
                                {run.workflow_name ?? `Workflow ${run.workflow_id}`}
                            </p>
                            <p className="text-xs text-muted-foreground">
                                Run {run.id.slice(0, 8)} · {run.trigger_type}
                            </p>
                        </div>
                        <RunStatusBadge status={run.status} pulse={run.status === 'running'} />
                    </Link>
                </li>
            ))}
        </ul>
    );
}
