import { Link } from 'react-router-dom';
import { ChevronLeft, ChevronRight } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { appRoutes } from '@/core/constants/routes';
import { RunStatusBadge } from '@/modules/monitoring/components/run-status-badge';
import type { RunStatus, WorkflowRun } from '@/modules/monitoring/types/run';
import { formatDurationMs } from '@/modules/monitoring/utils/format-duration';

interface RunsHistoryTableProps {
    runs: WorkflowRun[];
    isLoading?: boolean;
    page: number;
    lastPage: number;
    total: number;
    perPage: number;
    status: RunStatus | '';
    onPageChange: (page: number) => void;
    onStatusChange: (status: RunStatus | '') => void;
}

const statusOptions: { value: RunStatus | 'all'; label: string }[] = [
    { value: 'all', label: 'All statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'running', label: 'Running' },
    { value: 'success', label: 'Success' },
    { value: 'failed', label: 'Failed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'timed_out', label: 'Timed out' },
];

function RunsHistorySkeleton() {
    return (
        <div className="space-y-3">
            {Array.from({ length: 5 }).map((_, index) => (
                <Skeleton key={index} className="h-14 w-full" />
            ))}
        </div>
    );
}

export function RunsHistoryTable({
    runs,
    isLoading = false,
    page,
    lastPage,
    total,
    perPage,
    status,
    onPageChange,
    onStatusChange,
}: RunsHistoryTableProps) {
    const from = total === 0 ? 0 : (page - 1) * perPage + 1;
    const to = Math.min(page * perPage, total);

    return (
        <section className="space-y-3">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                    Run history
                </h2>
                <Select
                    value={status || 'all'}
                    onValueChange={(value) =>
                        onStatusChange(value === 'all' ? '' : (value as RunStatus))
                    }
                >
                    <SelectTrigger className="w-full sm:w-44">
                        <SelectValue placeholder="Filter by status" />
                    </SelectTrigger>
                    <SelectContent>
                        {statusOptions.map((option) => (
                            <SelectItem key={option.value} value={option.value}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            {isLoading ? (
                <RunsHistorySkeleton />
            ) : runs.length === 0 ? (
                <p className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                    No workflow runs match your filters.
                </p>
            ) : (
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/40">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Workflow</th>
                                <th className="hidden px-4 py-3 text-left font-medium md:table-cell">
                                    Trigger
                                </th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="hidden px-4 py-3 text-left font-medium lg:table-cell">
                                    Started
                                </th>
                                <th className="hidden px-4 py-3 text-left font-medium xl:table-cell">
                                    Duration
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {runs.map((run) => {
                                const durationMs =
                                    run.started_at && run.completed_at
                                        ? new Date(run.completed_at).getTime() -
                                          new Date(run.started_at).getTime()
                                        : null;

                                return (
                                    <tr key={run.id} className="border-b last:border-b-0">
                                        <td className="px-4 py-3">
                                            <Link
                                                to={appRoutes.monitoring.runDetail(run.id)}
                                                className="font-medium hover:underline"
                                            >
                                                {run.workflow_name ?? `Workflow ${run.workflow_id}`}
                                            </Link>
                                            <p className="text-xs text-muted-foreground">
                                                {run.id.slice(0, 8)}
                                            </p>
                                        </td>
                                        <td className="hidden px-4 py-3 capitalize text-muted-foreground md:table-cell">
                                            {run.trigger_type}
                                        </td>
                                        <td className="px-4 py-3">
                                            <RunStatusBadge
                                                status={run.status}
                                                pulse={run.status === 'running'}
                                            />
                                        </td>
                                        <td className="hidden px-4 py-3 text-muted-foreground lg:table-cell">
                                            {run.started_at
                                                ? new Date(run.started_at).toLocaleString()
                                                : '—'}
                                        </td>
                                        <td className="hidden px-4 py-3 text-muted-foreground xl:table-cell">
                                            {durationMs !== null && durationMs >= 0
                                                ? formatDurationMs(durationMs)
                                                : '—'}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            )}

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-sm text-muted-foreground">
                    Showing {from}–{to} of {total} run{total === 1 ? '' : 's'}
                </p>
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => onPageChange(page - 1)}
                        disabled={page <= 1}
                    >
                        <ChevronLeft className="size-4" />
                        Previous
                    </Button>
                    <span className="text-sm text-muted-foreground">
                        Page {page} of {lastPage || 1}
                    </span>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => onPageChange(page + 1)}
                        disabled={page >= lastPage}
                    >
                        Next
                        <ChevronRight className="size-4" />
                    </Button>
                </div>
            </div>
        </section>
    );
}
