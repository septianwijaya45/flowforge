import { Link } from 'react-router-dom';
import { Pencil, Play, Zap } from 'lucide-react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { appRoutes } from '@/core/constants/routes';
import { DeleteWorkflowDialog } from '@/modules/workflow/components/delete-workflow-dialog';
import { WorkflowStatusBadge } from '@/modules/workflow/components/workflow-status-badge';
import { useDeleteWorkflow } from '@/modules/workflow/hooks/use-delete-workflow';
import type { Workflow } from '@/modules/workflow/types/workflow';

interface WorkflowListProps {
    workflows: Workflow[];
    isLoading?: boolean;
    runningWorkflowId?: string | null;
    canWrite?: boolean;
    onRun: (workflow: Workflow) => void;
}

function WorkflowListSkeleton() {
    return (
        <div className="space-y-3">
            {Array.from({ length: 5 }).map((_, index) => (
                <Skeleton key={index} className="h-16 w-full" />
            ))}
        </div>
    );
}

export function WorkflowList({
    workflows,
    isLoading = false,
    runningWorkflowId = null,
    canWrite = false,
    onRun,
}: WorkflowListProps) {
    const deleteWorkflow = useDeleteWorkflow();

    const handleDelete = (workflow: Workflow) => {
        deleteWorkflow.mutate(workflow.id, {
            onSuccess: () => {
                toast.success(`Workflow "${workflow.name}" deleted`);
            },
            onError: (error) => {
                toast.error('Failed to delete workflow', {
                    description: error.message,
                });
            },
        });
    };

    if (isLoading) {
        return <WorkflowListSkeleton />;
    }

    if (workflows.length === 0) {
        return (
            <p className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                No workflows match your search or filters.
            </p>
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border">
            <table className="w-full text-sm">
                <thead className="border-b bg-muted/40">
                    <tr>
                        <th className="px-4 py-3 text-left font-medium">Name</th>
                        <th className="hidden px-4 py-3 text-left font-medium md:table-cell">
                            Slug
                        </th>
                        <th className="px-4 py-3 text-left font-medium">Status</th>
                        <th className="hidden px-4 py-3 text-left font-medium lg:table-cell">
                            Updated
                        </th>
                        <th className="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {workflows.map((workflow) => {
                        const isRunning = runningWorkflowId === workflow.id;
                        const canRun = workflow.status === 'active';

                        return (
                            <tr key={workflow.id} className="border-b last:border-b-0">
                                <td className="px-4 py-3">
                                    <p className="font-medium">{workflow.name}</p>
                                    {workflow.description ? (
                                        <p className="text-muted-foreground">{workflow.description}</p>
                                    ) : null}
                                </td>
                                <td className="hidden px-4 py-3 text-muted-foreground md:table-cell">
                                    {workflow.slug}
                                </td>
                                <td className="px-4 py-3">
                                    <WorkflowStatusBadge status={workflow.status} />
                                </td>
                                <td className="hidden px-4 py-3 text-muted-foreground lg:table-cell">
                                    {new Date(workflow.updated_at).toLocaleDateString()}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <div className="flex justify-end gap-2">
                                        <Button size="sm" variant="ghost" asChild>
                                            <Link to={appRoutes.workflow.triggers(workflow.id)}>
                                                <Zap className="size-4" />
                                                Triggers
                                            </Link>
                                        </Button>
                                        <Button size="sm" variant="ghost" asChild>
                                            <Link to={appRoutes.workflow.builder(workflow.id)}>
                                                <Pencil className="size-4" />
                                                Edit
                                            </Link>
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            disabled={!canRun || isRunning || !canWrite}
                                            onClick={() => onRun(workflow)}
                                        >
                                            <Play className="size-4" />
                                            {isRunning ? 'Running…' : 'Run'}
                                        </Button>
                                        {canWrite ? (
                                            <DeleteWorkflowDialog
                                                workflow={workflow}
                                                disabled={deleteWorkflow.isPending}
                                                onConfirm={() => handleDelete(workflow)}
                                            />
                                        ) : null}
                                    </div>
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}
