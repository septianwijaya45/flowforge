import type { RunStatus, StepStatus } from '@/modules/monitoring/types/run';

export const runStatusColors: Record<RunStatus, string> = {
    pending: 'text-muted-foreground',
    running: 'text-blue-600',
    success: 'text-green-600',
    failed: 'text-red-600',
    cancelled: 'text-amber-600',
    timed_out: 'text-orange-600',
};

export const runStatusBadgeVariant: Record<
    RunStatus,
    'default' | 'secondary' | 'outline' | 'destructive'
> = {
    pending: 'secondary',
    running: 'default',
    success: 'outline',
    failed: 'destructive',
    cancelled: 'secondary',
    timed_out: 'destructive',
};

export const stepStatusColors: Record<StepStatus, string> = {
    pending: 'bg-muted text-muted-foreground',
    running: 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200',
    success: 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200',
    failed: 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200',
    cancelled: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
};
