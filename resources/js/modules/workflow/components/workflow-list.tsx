import { Play } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { WorkflowStatusBadge } from '@/modules/workflow/components/workflow-status-badge';
import type { Workflow } from '@/modules/workflow/types/workflow';

interface WorkflowListProps {
    workflows: Workflow[];
    isLoading?: boolean;
    runningWorkflowId?: number | null;
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
    onRun,
}: WorkflowListProps) {
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
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        disabled={!canRun || isRunning}
                                        onClick={() => onRun(workflow)}
                                    >
                                        <Play className="size-4" />
                                        {isRunning ? 'Running…' : 'Run'}
                                    </Button>
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}
