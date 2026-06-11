import { useState } from 'react';
import { toast } from 'sonner';

import { useAuth } from '@/app/providers/auth-provider';
import { appRoutes } from '@/core/constants/routes';
import { canWrite } from '@/core/auth/permissions';
import { useDebounce } from '@/core/hooks/use-debounce';
import { WorkflowList } from '@/modules/workflow/components/workflow-list';
import { WorkflowListPagination } from '@/modules/workflow/components/workflow-list-pagination';
import { WorkflowListToolbar } from '@/modules/workflow/components/workflow-list-toolbar';
import { useRunWorkflow } from '@/modules/workflow/hooks/use-run-workflow';
import { useWorkflows } from '@/modules/workflow/hooks/use-workflows';
import type { Workflow, WorkflowStatus } from '@/modules/workflow/types/workflow';
import { PageHeader } from '@/shared/components/page-header';

const PER_PAGE = 15;

export function WorkflowsPage() {
    const { apiAuthReady, user } = useAuth();
    const userCanWrite = canWrite(user?.role as string | undefined);
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState<WorkflowStatus | ''>('');
    const debouncedSearch = useDebounce(search);

    const { data, isLoading, isError, error, isFetching } = useWorkflows(
        {
            page,
            per_page: PER_PAGE,
            search: debouncedSearch,
            status,
        },
        { enabled: apiAuthReady },
    );

    const runWorkflow = useRunWorkflow();

    const handleSearchChange = (value: string) => {
        setSearch(value);
        setPage(1);
    };

    const handleStatusChange = (value: WorkflowStatus | '') => {
        setStatus(value);
        setPage(1);
    };

    const handleRun = (workflow: Workflow) => {
        runWorkflow.mutate(workflow.id, {
            onSuccess: (run) => {
                toast.success(`Workflow "${workflow.name}" started`, {
                    description: `Run ${run.id} is ${run.status}.`,
                    action: {
                        label: 'Monitor',
                        onClick: () => {
                            window.location.href = appRoutes.monitoring.runDetail(run.id);
                        },
                    },
                });
            },
            onError: (runError) => {
                toast.error('Failed to run workflow', {
                    description: runError.message,
                });
            },
        });
    };

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Workflows"
                description="Design, version, and orchestrate automated processes."
            />

            <WorkflowListToolbar
                search={search}
                status={status}
                canWrite={userCanWrite}
                onSearchChange={handleSearchChange}
                onStatusChange={handleStatusChange}
            />

            {isError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                    {error.message}
                </p>
            ) : null}

            <WorkflowList
                workflows={data?.workflows ?? []}
                isLoading={isLoading || (isFetching && !data)}
                runningWorkflowId={runWorkflow.isPending ? runWorkflow.variables : null}
                canWrite={userCanWrite}
                onRun={handleRun}
            />

            {data?.pagination ? (
                <WorkflowListPagination
                    pagination={data.pagination}
                    onPageChange={setPage}
                />
            ) : null}
        </div>
    );
}
