export interface MonitoringMetricsTotals {
    completed: number;
    success: number;
    failed: number;
    cancelled: number;
    timed_out: number;
}

export interface MonitoringDailyMetric {
    date: string;
    runs: number;
    success: number;
    failed: number;
    avg_execution_time_ms: number | null;
}

export interface MonitoringMetrics {
    active_runs: number;
    success_rate: number;
    failure_rate: number;
    avg_execution_time_ms: number | null;
    totals: MonitoringMetricsTotals;
    daily: MonitoringDailyMetric[];
}
