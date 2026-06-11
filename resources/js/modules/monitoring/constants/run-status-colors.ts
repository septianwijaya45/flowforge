import type { RunStatus } from '@/modules/monitoring/types/run';

export const runStatusColors: Record<RunStatus, string> = {
    pending: 'text-muted-foreground',
    running: 'text-blue-600',
    completed: 'text-green-600',
    failed: 'text-red-600',
    cancelled: 'text-amber-600',
};
