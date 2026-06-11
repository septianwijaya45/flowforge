import { useState } from 'react';
import { Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Workflow } from '@/modules/workflow/types/workflow';

interface DeleteWorkflowDialogProps {
    workflow: Workflow;
    disabled?: boolean;
    onConfirm: () => void;
}

export function DeleteWorkflowDialog({
    workflow,
    disabled = false,
    onConfirm,
}: DeleteWorkflowDialogProps) {
    const [open, setOpen] = useState(false);

    const handleConfirm = () => {
        onConfirm();
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="ghost" disabled={disabled} className="text-destructive">
                    <Trash2 className="size-4" />
                    Delete
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete workflow</DialogTitle>
                    <DialogDescription>
                        This permanently removes <strong>{workflow.name}</strong>, its triggers,
                        versions, and run history. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" onClick={handleConfirm}>
                        Delete workflow
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
