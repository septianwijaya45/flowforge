import { Copy, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import {
    useDeleteTrigger,
    useUpdateTrigger,
} from '@/modules/workflow/hooks/use-workflow-triggers';
import type { WorkflowTrigger } from '@/modules/workflow/types/trigger';

interface TriggerListProps {
    workflowId: string;
    triggers: WorkflowTrigger[];
    isLoading?: boolean;
    canWrite?: boolean;
}

function TriggerListSkeleton() {
    return (
        <div className="space-y-3">
            {Array.from({ length: 3 }).map((_, index) => (
                <Skeleton key={index} className="h-28 w-full" />
            ))}
        </div>
    );
}

async function copyToClipboard(value: string, label: string) {
    try {
        await navigator.clipboard.writeText(value);
        toast.success(`${label} copied`);
    } catch {
        toast.error(`Could not copy ${label.toLowerCase()}`);
    }
}

export function TriggerList({
    workflowId,
    triggers,
    isLoading = false,
    canWrite = false,
}: TriggerListProps) {
    const updateTrigger = useUpdateTrigger(workflowId);
    const deleteTrigger = useDeleteTrigger(workflowId);

    if (isLoading) {
        return <TriggerListSkeleton />;
    }

    if (triggers.length === 0) {
        return (
            <p className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                No triggers configured yet. Add a manual, cron, or webhook trigger to start this
                workflow automatically.
            </p>
        );
    }

    const handleToggleActive = (trigger: WorkflowTrigger) => {
        updateTrigger.mutate(
            {
                triggerId: trigger.id,
                input: { is_active: !trigger.is_active },
            },
            {
                onError: (error) => {
                    toast.error('Failed to update trigger', {
                        description: error.message,
                    });
                },
            },
        );
    };

    const handleCronChange = (trigger: WorkflowTrigger, expression: string) => {
        updateTrigger.mutate({
            triggerId: trigger.id,
            input: { config: { expression } },
        });
    };

    const handleDelete = (trigger: WorkflowTrigger) => {
        deleteTrigger.mutate(trigger.id, {
            onSuccess: () => {
                toast.success(`Trigger "${trigger.name}" deleted`);
            },
            onError: (error) => {
                toast.error('Failed to delete trigger', {
                    description: error.message,
                });
            },
        });
    };

    return (
        <div className="space-y-4">
            {triggers.map((trigger) => (
                <div key={trigger.id} className="rounded-lg border p-4">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div className="flex flex-wrap items-center gap-2">
                                <p className="font-medium">{trigger.name}</p>
                                <Badge variant="secondary">{trigger.type}</Badge>
                                <Badge variant={trigger.is_active ? 'default' : 'outline'}>
                                    {trigger.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                            </div>
                            {trigger.last_triggered_at ? (
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Last run:{' '}
                                    {new Date(trigger.last_triggered_at).toLocaleString()}
                                </p>
                            ) : null}
                        </div>

                        {canWrite ? (
                            <div className="flex gap-2">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    disabled={updateTrigger.isPending}
                                    onClick={() => handleToggleActive(trigger)}
                                >
                                    {trigger.is_active ? 'Disable' : 'Enable'}
                                </Button>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    className="text-destructive"
                                    disabled={deleteTrigger.isPending}
                                    onClick={() => handleDelete(trigger)}
                                >
                                    <Trash2 className="size-4" />
                                    Delete
                                </Button>
                            </div>
                        ) : null}
                    </div>

                    {trigger.type === 'cron' ? (
                        <div className="mt-4 grid gap-2">
                            <Label htmlFor={`cron-${trigger.id}`}>Cron expression</Label>
                            <Input
                                id={`cron-${trigger.id}`}
                                defaultValue={trigger.config?.expression ?? ''}
                                readOnly={!canWrite}
                                onBlur={(event) => {
                                    const value = event.target.value.trim();
                                    if (
                                        canWrite &&
                                        value &&
                                        value !== (trigger.config?.expression ?? '')
                                    ) {
                                        handleCronChange(trigger, value);
                                    }
                                }}
                            />
                        </div>
                    ) : null}

                    {trigger.type === 'webhook' ? (
                        <div className="mt-4 space-y-3">
                            {trigger.webhook_url ? (
                                <div className="grid gap-2">
                                    <Label>Webhook URL</Label>
                                    <div className="flex gap-2">
                                        <Input value={trigger.webhook_url} readOnly />
                                        <Button
                                            type="button"
                                            size="icon"
                                            variant="outline"
                                            onClick={() =>
                                                copyToClipboard(trigger.webhook_url!, 'Webhook URL')
                                            }
                                        >
                                            <Copy className="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            ) : null}
                            {trigger.webhook_token ? (
                                <div className="grid gap-2">
                                    <Label>Webhook token</Label>
                                    <div className="flex gap-2">
                                        <Input value={trigger.webhook_token} readOnly />
                                        <Button
                                            type="button"
                                            size="icon"
                                            variant="outline"
                                            onClick={() =>
                                                copyToClipboard(
                                                    trigger.webhook_token!,
                                                    'Webhook token',
                                                )
                                            }
                                        >
                                            <Copy className="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            ) : null}
                            <p className="text-xs text-muted-foreground">
                                Send an HTTP POST to the webhook URL to start this workflow.
                            </p>
                        </div>
                    ) : null}

                    {trigger.type === 'manual' ? (
                        <p className="mt-3 text-sm text-muted-foreground">
                            Run this workflow from the list page or the manual trigger API endpoint.
                        </p>
                    ) : null}
                </div>
            ))}
        </div>
    );
}
