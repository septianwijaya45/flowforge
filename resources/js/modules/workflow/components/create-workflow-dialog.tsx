import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { appRoutes } from '@/core/constants/routes';
import { useCreateWorkflow } from '@/modules/workflow/hooks/use-create-workflow';

interface CreateWorkflowDialogProps {
    disabled?: boolean;
}

export function CreateWorkflowDialog({ disabled = false }: CreateWorkflowDialogProps) {
    const [open, setOpen] = useState(false);
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const createWorkflow = useCreateWorkflow();

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();

        if (!name.trim()) {
            return;
        }

        createWorkflow.mutate(
            {
                name: name.trim(),
                description: description.trim() || undefined,
            },
            {
                onSuccess: (workflow) => {
                    toast.success(`Workflow "${workflow.name}" created`);
                    setOpen(false);
                    setName('');
                    setDescription('');
                    router.visit(appRoutes.workflow.builder(workflow.id));
                },
                onError: (error) => {
                    toast.error('Failed to create workflow', {
                        description: error.message,
                    });
                },
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button disabled={disabled}>
                    <Plus className="size-4" />
                    New workflow
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create workflow</DialogTitle>
                        <DialogDescription>
                            Add a new workflow draft. You can design steps in the builder after
                            creation.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="workflow-name">Name</Label>
                            <Input
                                id="workflow-name"
                                value={name}
                                onChange={(event) => setName(event.target.value)}
                                placeholder="Order fulfillment"
                                autoFocus
                                required
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="workflow-description">Description</Label>
                            <Input
                                id="workflow-description"
                                value={description}
                                onChange={(event) => setDescription(event.target.value)}
                                placeholder="Optional summary"
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setOpen(false)}
                            disabled={createWorkflow.isPending}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={createWorkflow.isPending || !name.trim()}>
                            {createWorkflow.isPending ? 'Creating…' : 'Create'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
