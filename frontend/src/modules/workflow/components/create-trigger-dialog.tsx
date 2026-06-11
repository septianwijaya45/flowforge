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
import { useCreateTrigger } from '@/modules/workflow/hooks/use-workflow-triggers';
import type { TriggerType } from '@/modules/workflow/types/trigger';

interface CreateTriggerDialogProps {
    workflowId: string;
    disabled?: boolean;
}

const triggerTypeOptions: { value: TriggerType; label: string; hint: string }[] = [
    { value: 'manual', label: 'Manual', hint: 'Run from the UI or API on demand.' },
    { value: 'cron', label: 'Cron schedule', hint: 'Runs on a recurring cron expression.' },
    { value: 'webhook', label: 'Webhook', hint: 'Runs when an HTTP POST hits the webhook URL.' },
];

export function CreateTriggerDialog({ workflowId, disabled = false }: CreateTriggerDialogProps) {
    const [open, setOpen] = useState(false);
    const [type, setType] = useState<TriggerType>('manual');
    const [name, setName] = useState('');
    const [cronExpression, setCronExpression] = useState('0 * * * *');
    const createTrigger = useCreateTrigger(workflowId);

    const selectedHint = triggerTypeOptions.find((option) => option.value === type)?.hint;

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();

        if (!name.trim()) {
            return;
        }

        createTrigger.mutate(
            {
                type,
                name: name.trim(),
                is_active: true,
                config: type === 'cron' ? { expression: cronExpression.trim() } : undefined,
            },
            {
                onSuccess: (trigger) => {
                    toast.success(`Trigger "${trigger.name}" created`);
                    if (trigger.type === 'webhook' && trigger.webhook_url) {
                        toast.message('Webhook URL ready', {
                            description: trigger.webhook_url,
                        });
                    }
                    setOpen(false);
                    setName('');
                    setType('manual');
                    setCronExpression('0 * * * *');
                },
                onError: (error) => {
                    toast.error('Failed to create trigger', {
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
                    Add trigger
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Add trigger</DialogTitle>
                        <DialogDescription>
                            Configure how this workflow starts — manually, on a schedule, or via
                            webhook.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="trigger-type">Type</Label>
                            <Select
                                value={type}
                                onValueChange={(value) => setType(value as TriggerType)}
                            >
                                <SelectTrigger id="trigger-type">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {triggerTypeOptions.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {selectedHint ? (
                                <p className="text-xs text-muted-foreground">{selectedHint}</p>
                            ) : null}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="trigger-name">Name</Label>
                            <Input
                                id="trigger-name"
                                value={name}
                                onChange={(event) => setName(event.target.value)}
                                placeholder="Nightly sync"
                                required
                            />
                        </div>

                        {type === 'cron' ? (
                            <div className="grid gap-2">
                                <Label htmlFor="cron-expression">Cron expression</Label>
                                <Input
                                    id="cron-expression"
                                    value={cronExpression}
                                    onChange={(event) => setCronExpression(event.target.value)}
                                    placeholder="0 2 * * *"
                                    required
                                />
                                <p className="text-xs text-muted-foreground">
                                    Standard 5-field cron, e.g. <code>0 2 * * *</code> for 2:00 AM
                                    daily.
                                </p>
                            </div>
                        ) : null}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setOpen(false)}
                            disabled={createTrigger.isPending}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={createTrigger.isPending || !name.trim()}>
                            {createTrigger.isPending ? 'Creating…' : 'Create'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
