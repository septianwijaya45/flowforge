import { useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { useImportGeneratedWorkflow } from '@/modules/ai/hooks/use-import-generated-workflow';
import { useWorkflows } from '@/modules/workflow/hooks/use-workflows';
import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';

interface ImportWorkflowDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    definition: WorkflowDefinition;
    suggestedName: string;
}

type ImportMode = 'new' | 'existing';

export function ImportWorkflowDialog({
    open,
    onOpenChange,
    definition,
    suggestedName,
}: ImportWorkflowDialogProps) {
    const [mode, setMode] = useState<ImportMode>('new');
    const [name, setName] = useState(suggestedName);
    const [existingWorkflowId, setExistingWorkflowId] = useState<string>('');

    const importWorkflow = useImportGeneratedWorkflow();
    const { data: workflowsData, isLoading: isLoadingWorkflows } = useWorkflows(
        { page: 1, per_page: 50 },
        { enabled: open && mode === 'existing' },
    );

    useEffect(() => {
        if (open) {
            setName(suggestedName);
            setMode('new');
            setExistingWorkflowId('');
        }
    }, [open, suggestedName]);

    const handleImport = () => {
        if (mode === 'new' && !name.trim()) {
            return;
        }

        if (mode === 'existing' && !existingWorkflowId) {
            return;
        }

        importWorkflow.mutate(
            {
                name: name.trim() || suggestedName,
                definition,
                existingWorkflowId: mode === 'existing' ? existingWorkflowId : undefined,
            },
            {
                onSuccess: () => onOpenChange(false),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Import into Workflow Builder</DialogTitle>
                    <DialogDescription>
                        Create a new workflow or add a version to an existing one, then open the
                        visual builder.
                    </DialogDescription>
                </DialogHeader>

                <div className="flex flex-col gap-4 py-2">
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="import-mode">Import target</Label>
                        <Select
                            value={mode}
                            onValueChange={(value) => setMode(value as ImportMode)}
                        >
                            <SelectTrigger id="import-mode">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="new">Create new workflow</SelectItem>
                                <SelectItem value="existing">Use existing workflow</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    {mode === 'new' ? (
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="workflow-name">Workflow name</Label>
                            <Input
                                id="workflow-name"
                                value={name}
                                onChange={(event) => setName(event.target.value)}
                                placeholder="Website health monitor"
                            />
                        </div>
                    ) : (
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="existing-workflow">Workflow</Label>
                            <Select
                                value={existingWorkflowId}
                                onValueChange={setExistingWorkflowId}
                                disabled={isLoadingWorkflows}
                            >
                                <SelectTrigger id="existing-workflow">
                                    <SelectValue placeholder="Select a workflow" />
                                </SelectTrigger>
                                <SelectContent>
                                    {(workflowsData?.workflows ?? []).map((workflow) => (
                                        <SelectItem key={workflow.id} value={workflow.id}>
                                            {workflow.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    )}
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button onClick={handleImport} disabled={importWorkflow.isPending}>
                        {importWorkflow.isPending ? <Spinner /> : null}
                        Open in Builder
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
