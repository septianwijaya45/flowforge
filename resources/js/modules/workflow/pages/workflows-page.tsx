import { useAuth } from '@/app/providers/auth-provider';
import { WorkflowList } from '@/modules/workflow/components/workflow-list';
import { useWorkflows } from '@/modules/workflow/hooks/use-workflows';
import { PageHeader } from '@/shared/components/page-header';

export function WorkflowsPage() {
    const { isAuthenticated } = useAuth();
    const { data: workflows, isLoading, isError, error } = useWorkflows(undefined, {
        enabled: isAuthenticated,
    });

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Workflows"
                description="Design, version, and orchestrate automated processes."
            />

            {isLoading ? (
                <p className="text-sm text-muted-foreground">Loading workflows…</p>
            ) : null}

            {isError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {(error as Error).message}
                </p>
            ) : null}

            {workflows ? <WorkflowList workflows={workflows} /> : null}
        </div>
    );
}
