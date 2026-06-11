import { Activity, Clock, ThumbsDown, ThumbsUp } from 'lucide-react';

import { Skeleton } from '@/components/ui/skeleton';
import type { MonitoringMetrics } from '@/modules/monitoring/types/metrics';
import { formatDurationMs } from '@/modules/monitoring/utils/format-duration';

interface MetricsSummaryCardsProps {
    metrics?: MonitoringMetrics;
    isLoading?: boolean;
}

export function MetricsSummaryCards({ metrics, isLoading = false }: MetricsSummaryCardsProps) {
    if (isLoading) {
        return (
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {Array.from({ length: 4 }).map((_, index) => (
                    <Skeleton key={index} className="h-28 w-full" />
                ))}
            </div>
        );
    }

    if (!metrics) {
        return null;
    }

    const cards = [
        {
            label: 'Active runs',
            value: String(metrics.active_runs),
            hint: 'Pending or running now',
            icon: Activity,
            accent: 'text-blue-600',
        },
        {
            label: 'Success rate',
            value: `${metrics.success_rate.toFixed(1)}%`,
            hint: `${metrics.totals.success} of ${metrics.totals.completed} completed`,
            icon: ThumbsUp,
            accent: 'text-green-600',
        },
        {
            label: 'Failure rate',
            value: `${metrics.failure_rate.toFixed(1)}%`,
            hint: `${metrics.totals.failed + metrics.totals.timed_out} failed or timed out`,
            icon: ThumbsDown,
            accent: 'text-red-600',
        },
        {
            label: 'Avg execution time',
            value: formatDurationMs(metrics.avg_execution_time_ms),
            hint: 'Completed runs in period',
            icon: Clock,
            accent: 'text-violet-600',
        },
    ];

    return (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => {
                const Icon = card.icon;

                return (
                    <div key={card.label} className="rounded-lg border bg-card p-4 shadow-xs">
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="text-sm text-muted-foreground">{card.label}</p>
                                <p className="mt-2 text-2xl font-semibold tracking-tight">{card.value}</p>
                                <p className="mt-1 text-xs text-muted-foreground">{card.hint}</p>
                            </div>
                            <div className={`rounded-md bg-muted p-2 ${card.accent}`}>
                                <Icon className="size-4" />
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
