import { useParams } from 'react-router-dom';

import { Skeleton } from '@/components/ui/skeleton';
import { useWorkflowBuilder } from '@/modules/workflow/hooks/use-workflow-builder';
import { WorkflowTriggersPage } from '@/modules/workflow/pages/workflow-triggers-page';

function WorkflowTriggersRouteContent({ workflowId }: { workflowId: string }) {
    const { workflow, isLoading, isError, error } = useWorkflowBuilder(workflowId);

    if (isLoading) {
        return (
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <Skeleton className="h-8 w-48" />
                <Skeleton className="h-24 w-full" />
                <Skeleton className="h-64 w-full" />
            </div>
        );
    }

    if (isError) {
        return (
            <p className="m-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                {error?.message ?? 'Failed to load workflow triggers.'}
            </p>
        );
    }

    if (!workflow) {
        return null;
    }

    return <WorkflowTriggersPage workflowId={workflowId} workflowName={workflow.name} />;
}

export function WorkflowTriggersRoute() {
    const { workflowId } = useParams<{ workflowId: string }>();

    if (!workflowId) {
        return null;
    }

    return <WorkflowTriggersRouteContent workflowId={workflowId} />;
}
