import {
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip
    
} from 'chart.js';
import type {ChartOptions} from 'chart.js';
import { Line } from 'react-chartjs-2';

import { Skeleton } from '@/components/ui/skeleton';
import type { MonitoringMetrics } from '@/modules/monitoring/types/metrics';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend);

interface ExecutionTrendChartProps {
    metrics?: MonitoringMetrics;
    isLoading?: boolean;
}

export function ExecutionTrendChart({ metrics, isLoading = false }: ExecutionTrendChartProps) {
    if (isLoading) {
        return <Skeleton className="h-72 w-full" />;
    }

    if (!metrics) {
        return null;
    }

    const labels = metrics.daily.map((point) =>
        new Date(point.date).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }),
    );

    const data = {
        labels,
        datasets: [
            {
                label: 'Success',
                data: metrics.daily.map((point) => point.success),
                borderColor: 'rgb(22, 163, 74)',
                backgroundColor: 'rgba(22, 163, 74, 0.15)',
                tension: 0.3,
            },
            {
                label: 'Failed',
                data: metrics.daily.map((point) => point.failed),
                borderColor: 'rgb(220, 38, 38)',
                backgroundColor: 'rgba(220, 38, 38, 0.15)',
                tension: 0.3,
            },
            {
                label: 'Avg time (s)',
                data: metrics.daily.map((point) =>
                    point.avg_execution_time_ms !== null
                        ? Number((point.avg_execution_time_ms / 1000).toFixed(2))
                        : null,
                ),
                borderColor: 'rgb(124, 58, 237)',
                backgroundColor: 'rgba(124, 58, 237, 0.15)',
                tension: 0.3,
                yAxisID: 'y1',
            },
        ],
    };

    const options: ChartOptions<'line'> = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Runs' },
                ticks: { precision: 0 },
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                grid: { drawOnChartArea: false },
                title: { display: true, text: 'Seconds' },
            },
        },
        plugins: {
            legend: {
                position: 'bottom',
            },
        },
    };

    return (
        <div className="rounded-lg border bg-card p-4">
            <h3 className="mb-4 text-sm font-semibold">Execution trend (30 days)</h3>
            <div className="h-72">
                <Line data={data} options={options} />
            </div>
        </div>
    );
}
