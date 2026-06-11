import {
    ArcElement,
    Chart as ChartJS,
    Legend,
    Tooltip
    
} from 'chart.js';
import type {ChartOptions} from 'chart.js';
import { Doughnut } from 'react-chartjs-2';

import { Skeleton } from '@/components/ui/skeleton';
import type { MonitoringMetrics } from '@/modules/monitoring/types/metrics';

ChartJS.register(ArcElement, Tooltip, Legend);

interface RunOutcomesChartProps {
    metrics?: MonitoringMetrics;
    isLoading?: boolean;
}

export function RunOutcomesChart({ metrics, isLoading = false }: RunOutcomesChartProps) {
    if (isLoading) {
        return <Skeleton className="h-72 w-full" />;
    }

    if (!metrics) {
        return null;
    }

    const { totals, active_runs } = metrics;
    const data = {
        labels: ['Success', 'Failed', 'Timed out', 'Cancelled', 'Active'],
        datasets: [
            {
                data: [
                    totals.success,
                    totals.failed,
                    totals.timed_out,
                    totals.cancelled,
                    active_runs,
                ],
                backgroundColor: [
                    'rgba(22, 163, 74, 0.85)',
                    'rgba(220, 38, 38, 0.85)',
                    'rgba(234, 88, 12, 0.85)',
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(37, 99, 235, 0.85)',
                ],
                borderWidth: 0,
            },
        ],
    };

    const options: ChartOptions<'doughnut'> = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
        },
    };

    return (
        <div className="rounded-lg border bg-card p-4">
            <h3 className="mb-4 text-sm font-semibold">Run outcomes</h3>
            <div className="h-64">
                <Doughnut data={data} options={options} />
            </div>
        </div>
    );
}
