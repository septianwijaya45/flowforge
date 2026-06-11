import { useState } from 'react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useCreateSchedule } from '@/modules/scheduler/hooks/use-schedules';
import type { Workflow } from '@/modules/workflow/types/workflow';

interface CreateScheduleDialogProps {
    workflows: Workflow[];
    disabled?: boolean;
}

export function CreateScheduleDialog({
    workflows,
    disabled = false,
}: CreateScheduleDialogProps) {
    const [open, setOpen] = useState(false);
    const [workflowId, setWorkflowId] = useState('');
    const [name, setName] = useState('');
    const [cronExpression, setCronExpression] = useState('0 2 * * *');
    const createSchedule = useCreateSchedule();

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();

        if (!workflowId || !name.trim() || !cronExpression.trim()) {
            return;
        }

        createSchedule.mutate(
            {
                workflow_id: workflowId,
                name: name.trim(),
                cron_expression: cronExpression.trim(),
                is_active: true,
            },
            {
                onSuccess: (schedule) => {
                    toast.success(`Schedule "${schedule.name}" created`);
                    setOpen(false);
                    setWorkflowId('');
                    setName('');
                    setCronExpression('0 2 * * *');
                },
                onError: (error) => {
                    toast.error('Failed to create schedule', {
                        description: error.message,
                    });
                },
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button disabled={disabled || workflows.length === 0}>
                    <Plus className="size-4" />
                    New schedule
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create schedule</DialogTitle>
                        <DialogDescription>
                            Attach a cron expression to a workflow. Schedules appear here and in
                            the workflow&apos;s trigger list.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="schedule-workflow">Workflow</Label>
                            <Select value={workflowId} onValueChange={setWorkflowId}>
                                <SelectTrigger id="schedule-workflow">
                                    <SelectValue placeholder="Select workflow" />
                                </SelectTrigger>
                                <SelectContent>
                                    {workflows.map((workflow) => (
                                        <SelectItem key={workflow.id} value={workflow.id}>
                                            {workflow.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="schedule-name">Name</Label>
                            <Input
                                id="schedule-name"
                                value={name}
                                onChange={(event) => setName(event.target.value)}
                                placeholder="Nightly sync"
                                required
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="schedule-cron">Cron expression</Label>
                            <Input
                                id="schedule-cron"
                                value={cronExpression}
                                onChange={(event) => setCronExpression(event.target.value)}
                                placeholder="0 2 * * *"
                                required
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setOpen(false)}
                            disabled={createSchedule.isPending}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            disabled={
                                createSchedule.isPending ||
                                !workflowId ||
                                !name.trim() ||
                                !cronExpression.trim()
                            }
                        >
                            {createSchedule.isPending ? 'Creating…' : 'Create'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
