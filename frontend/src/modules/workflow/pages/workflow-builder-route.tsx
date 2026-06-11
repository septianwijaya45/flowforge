import { useParams } from 'react-router-dom';

import { WorkflowBuilderPage } from '@/modules/workflow/pages/workflow-builder-page';

export function WorkflowBuilderRoute() {
    const { workflowId } = useParams<{ workflowId: string }>();

    if (!workflowId) {
        return null;
    }

    return <WorkflowBuilderPage workflowId={workflowId} />;
}
