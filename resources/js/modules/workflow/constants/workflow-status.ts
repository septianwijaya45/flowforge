import type { WorkflowStatus } from '@/modules/workflow/types/workflow';

export const workflowStatusOptions: { label: string; value: WorkflowStatus | 'all' }[] = [
    { label: 'All statuses', value: 'all' },
    { label: 'Active', value: 'active' },
    { label: 'Draft', value: 'draft' },
    { label: 'Archived', value: 'archived' },
    { label: 'Disabled', value: 'disabled' },
];

export const workflowStatusLabels: Record<WorkflowStatus, string> = {
    draft: 'Draft',
    active: 'Active',
    archived: 'Archived',
    disabled: 'Disabled',
};
