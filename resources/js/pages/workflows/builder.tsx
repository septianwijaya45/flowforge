import { Head } from '@inertiajs/react';

import { WorkflowBuilderPage } from '@/modules/workflow/pages/workflow-builder-page';

type Props = {
    workflowId: string;
    workflowName: string;
};

export default function WorkflowBuilder({ workflowId, workflowName }: Props) {
    return (
        <>
            <Head title={`Builder · ${workflowName}`} />
            <WorkflowBuilderPage workflowId={workflowId} />
        </>
    );
}

WorkflowBuilder.layout = {
    breadcrumbs: [
        { title: 'Workflows', href: '/workflows' },
        { title: 'Builder', href: '#' },
    ],
};
