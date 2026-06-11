import { Link } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';

import { useAuth } from '@/app/providers/auth-provider';
import { Button } from '@/components/ui/button';
import { appRoutes } from '@/core/constants/routes';
import { canWrite } from '@/core/auth/permissions';
import { CreateTriggerDialog } from '@/modules/workflow/components/create-trigger-dialog';
import { TriggerList } from '@/modules/workflow/components/trigger-list';
import { useWorkflowTriggers } from '@/modules/workflow/hooks/use-workflow-triggers';
import { PageHeader } from '@/shared/components/page-header';

interface WorkflowTriggersPageProps {
    workflowId: string;
    workflowName: string;
}

export function WorkflowTriggersPage({ workflowId, workflowName }: WorkflowTriggersPageProps) {
    const { apiAuthReady, user } = useAuth();
    const userCanWrite = canWrite(user?.role as string | undefined);

    const { data, isLoading, isError, error } = useWorkflowTriggers(workflowId, {
        enabled: apiAuthReady,
    });

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <div className="flex flex-wrap items-center gap-3">
                <Button size="sm" variant="ghost" asChild>
                    <Link to={appRoutes.workflow.index}>
                        <ArrowLeft className="size-4" />
                        Back to workflows
                    </Link>
                </Button>
            </div>

            <PageHeader
                title={`Triggers — ${workflowName}`}
                description="Manage manual, cron, and webhook triggers for this workflow."
                actions={
                    userCanWrite ? (
                        <CreateTriggerDialog workflowId={workflowId} />
                    ) : undefined
                }
            />

            {isError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {error.message}
                </p>
            ) : null}

            <TriggerList
                workflowId={workflowId}
                triggers={data ?? []}
                isLoading={isLoading}
                canWrite={userCanWrite}
            />
        </div>
    );
}
