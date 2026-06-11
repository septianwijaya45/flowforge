import { Head } from '@inertiajs/react';

import { WorkflowsPage } from '@/modules/workflow/pages/workflows-page';

export default function Workflows() {
    return (
        <>
            <Head title="Workflows" />
            <WorkflowsPage />
        </>
    );
}

Workflows.layout = {
    breadcrumbs: [{ title: 'Workflows', href: '/workflows' }],
};
