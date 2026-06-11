import { Badge } from '@/components/ui/badge';
import { workflowStatusLabels } from '@/modules/workflow/constants/workflow-status';
import type { WorkflowStatus } from '@/modules/workflow/types/workflow';

const statusVariant: Record<
    WorkflowStatus,
    'default' | 'secondary' | 'outline' | 'destructive'
> = {
    active: 'default',
    draft: 'secondary',
    archived: 'outline',
    disabled: 'destructive',
};

interface WorkflowStatusBadgeProps {
    status: WorkflowStatus;
}

export function WorkflowStatusBadge({ status }: WorkflowStatusBadgeProps) {
    return <Badge variant={statusVariant[status]}>{workflowStatusLabels[status]}</Badge>;
}
