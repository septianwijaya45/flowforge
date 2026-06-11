import { cn } from '@/lib/utils';
import { stepStatusColors } from '@/modules/monitoring/constants/run-status-colors';
import type { WorkflowRunStep } from '@/modules/monitoring/types/run';

interface RunStepTimelineProps {
    steps: WorkflowRunStep[];
}

export function RunStepTimeline({ steps }: RunStepTimelineProps) {
    if (steps.length === 0) {
        return (
            <p className="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                Waiting for execution steps…
            </p>
        );
    }

    return (
        <ol className="space-y-3">
            {steps.map((step, index) => (
                <li
                    key={step.id}
                    className={cn(
                        'flex items-start gap-4 rounded-lg border p-4 transition-colors',
                        step.status === 'running' && 'border-blue-300 bg-blue-50/50 dark:border-blue-900 dark:bg-blue-950/20',
                    )}
                >
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium">
                        {(step.execution_order ?? index) + 1}
                    </div>

                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                            <p className="font-medium">{step.node_label ?? step.node_id}</p>
                            <span
                                className={cn(
                                    'rounded-full px-2 py-0.5 text-xs font-medium capitalize',
                                    stepStatusColors[step.status],
                                )}
                            >
                                {step.status}
                            </span>
                            <span className="text-xs text-muted-foreground">{step.node_type}</span>
                        </div>

                        <div className="mt-1 flex flex-wrap gap-3 text-xs text-muted-foreground">
                            {step.started_at ? (
                                <span>Started {formatTime(step.started_at)}</span>
                            ) : null}
                            {step.duration_ms !== null ? (
                                <span>{step.duration_ms}ms</span>
                            ) : null}
                        </div>

                        {step.error ? (
                            <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                {String(step.error.message ?? 'Step failed')}
                            </p>
                        ) : null}
                    </div>
                </li>
            ))}
        </ol>
    );
}

function formatTime(iso: string): string {
    return new Date(iso).toLocaleTimeString();
}
