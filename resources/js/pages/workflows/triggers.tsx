import { Head } from '@inertiajs/react';

import { WorkflowTriggersPage } from '@/modules/workflow/pages/workflow-triggers-page';

interface WorkflowTriggersProps {
    workflowId: string;
    workflowName: string;
}

export default function WorkflowTriggers({ workflowId, workflowName }: WorkflowTriggersProps) {
    return (
        <>
            <Head title={`Triggers — ${workflowName}`} />
            <WorkflowTriggersPage workflowId={workflowId} workflowName={workflowName} />
        </>
    );
}

WorkflowTriggers.layout = {
    breadcrumbs: [
        { title: 'Workflows', href: '/workflows' },
        { title: 'Triggers', href: '#' },
    ],
};
