import { Badge } from '@/components/ui/badge';
import type { Workflow } from '@/modules/workflow/types/workflow';

interface WorkflowListProps {
    workflows: Workflow[];
}

export function WorkflowList({ workflows }: WorkflowListProps) {
    if (workflows.length === 0) {
        return (
            <p className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                No workflows yet. Create your first workflow to get started.
            </p>
        );
    }

    return (
        <ul className="grid gap-3">
            {workflows.map((workflow) => (
                <li
                    key={workflow.id}
                    className="flex items-center justify-between rounded-lg border p-4"
                >
                    <div>
                        <p className="font-medium">{workflow.name}</p>
                        {workflow.description ? (
                            <p className="text-sm text-muted-foreground">{workflow.description}</p>
                        ) : null}
                    </div>
                    <Badge variant="secondary">{workflow.status}</Badge>
                </li>
            ))}
        </ul>
    );
}
