import { Link } from 'react-router-dom';
import { Trash2 } from 'lucide-react';
import { toast } from 'sonner';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { appRoutes } from '@/core/constants/routes';
import { useDeleteSchedule, useUpdateSchedule } from '@/modules/scheduler/hooks/use-schedules';
import type { Schedule } from '@/modules/scheduler/types/schedule';

interface ScheduleListProps {
    schedules: Schedule[];
    isLoading?: boolean;
    canWrite?: boolean;
}

function ScheduleListSkeleton() {
    return (
        <div className="space-y-3">
            {Array.from({ length: 4 }).map((_, index) => (
                <Skeleton key={index} className="h-20 w-full" />
            ))}
        </div>
    );
}

export function ScheduleList({
    schedules,
    isLoading = false,
    canWrite = false,
}: ScheduleListProps) {
    const updateSchedule = useUpdateSchedule();
    const deleteSchedule = useDeleteSchedule();

    if (isLoading) {
        return <ScheduleListSkeleton />;
    }

    if (schedules.length === 0) {
        return (
            <p className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                No cron schedules yet. Create one to run workflows automatically.
            </p>
        );
    }

    const handleToggleActive = (schedule: Schedule) => {
        updateSchedule.mutate(
            {
                scheduleId: schedule.id,
                input: { is_active: !schedule.is_active },
            },
            {
                onError: (error) => {
                    toast.error('Failed to update schedule', {
                        description: error.message,
                    });
                },
            },
        );
    };

    const handleCronBlur = (schedule: Schedule, expression: string) => {
        if (expression && expression !== (schedule.cron_expression ?? '')) {
            updateSchedule.mutate({
                scheduleId: schedule.id,
                input: { cron_expression: expression },
            });
        }
    };

    const handleDelete = (schedule: Schedule) => {
        deleteSchedule.mutate(schedule.id, {
            onSuccess: () => {
                toast.success(`Schedule "${schedule.name}" deleted`);
            },
            onError: (error) => {
                toast.error('Failed to delete schedule', {
                    description: error.message,
                });
            },
        });
    };

    return (
        <div className="overflow-hidden rounded-lg border">
            <table className="w-full text-sm">
                <thead className="border-b bg-muted/40">
                    <tr>
                        <th className="px-4 py-3 text-left font-medium">Name</th>
                        <th className="hidden px-4 py-3 text-left font-medium md:table-cell">
                            Workflow
                        </th>
                        <th className="px-4 py-3 text-left font-medium">Cron</th>
                        <th className="hidden px-4 py-3 text-left font-medium lg:table-cell">
                            Next run
                        </th>
                        <th className="px-4 py-3 text-left font-medium">Status</th>
                        <th className="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {schedules.map((schedule) => (
                        <tr key={schedule.id} className="border-b last:border-b-0">
                            <td className="px-4 py-3 font-medium">{schedule.name}</td>
                            <td className="hidden px-4 py-3 md:table-cell">
                                {schedule.workflow_name ? (
                                    <Link
                                        to={appRoutes.workflow.triggers(schedule.workflow_id)}
                                        className="text-primary hover:underline"
                                    >
                                        {schedule.workflow_name}
                                    </Link>
                                ) : (
                                    '—'
                                )}
                            </td>
                            <td className="px-4 py-3">
                                {canWrite ? (
                                    <Input
                                        defaultValue={schedule.cron_expression ?? ''}
                                        className="font-mono text-xs"
                                        onBlur={(event) =>
                                            handleCronBlur(schedule, event.target.value.trim())
                                        }
                                    />
                                ) : (
                                    <code className="text-xs">{schedule.cron_expression}</code>
                                )}
                            </td>
                            <td className="hidden px-4 py-3 text-muted-foreground lg:table-cell">
                                {schedule.next_run_at
                                    ? new Date(schedule.next_run_at).toLocaleString()
                                    : '—'}
                            </td>
                            <td className="px-4 py-3">
                                <Badge variant={schedule.is_active ? 'default' : 'outline'}>
                                    {schedule.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                            </td>
                            <td className="px-4 py-3 text-right">
                                {canWrite ? (
                                    <div className="flex justify-end gap-2">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            disabled={updateSchedule.isPending}
                                            onClick={() => handleToggleActive(schedule)}
                                        >
                                            {schedule.is_active ? 'Disable' : 'Enable'}
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            className="text-destructive"
                                            disabled={deleteSchedule.isPending}
                                            onClick={() => handleDelete(schedule)}
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    </div>
                                ) : null}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
