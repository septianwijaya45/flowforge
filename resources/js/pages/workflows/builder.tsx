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
            <div className="-m-4 h-[calc(100vh-3rem)] md:-m-6">
                <WorkflowBuilderPage workflowId={workflowId} />
            </div>
        </>
    );
}

WorkflowBuilder.layout = {
    breadcrumbs: [
        { title: 'Workflows', href: '/workflows' },
        { title: 'Builder', href: '#' },
    ],
};
