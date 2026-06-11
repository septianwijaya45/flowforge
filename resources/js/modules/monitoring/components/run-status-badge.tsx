import { Badge } from '@/components/ui/badge';
import { runStatusBadgeVariant } from '@/modules/monitoring/constants/run-status-colors';
import type { RunStatus } from '@/modules/monitoring/types/run';

interface RunStatusBadgeProps {
    status: RunStatus;
    pulse?: boolean;
}

const statusLabels: Record<RunStatus, string> = {
    pending: 'Pending',
    running: 'Running',
    success: 'Success',
    failed: 'Failed',
    cancelled: 'Cancelled',
    timed_out: 'Timed out',
};

export function RunStatusBadge({ status, pulse = false }: RunStatusBadgeProps) {
    return (
        <Badge variant={runStatusBadgeVariant[status]} className="gap-1.5 capitalize">
            {pulse && status === 'running' ? (
                <span className="relative flex size-2">
                    <span className="absolute inline-flex size-full animate-ping rounded-full bg-blue-400 opacity-75" />
                    <span className="relative inline-flex size-2 rounded-full bg-blue-500" />
                </span>
            ) : null}
            {statusLabels[status]}
        </Badge>
    );
}
