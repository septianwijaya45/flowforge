import { useQueryClient } from '@tanstack/react-query';
import { useEffect } from 'react';

import { tenantStorage } from '@/core/auth/tenant-storage';
import { getEcho } from '@/core/realtime/echo-client';
import { monitoringKeys } from '@/modules/monitoring/query-keys';
import type { WorkflowRun, WorkflowRunStep } from '@/modules/monitoring/types/run';

interface RunUpdatedEvent {
    run: WorkflowRun;
}

interface StepUpdatedEvent {
    run_id: string;
    step: WorkflowRunStep;
}

export function useRunRealtime(runId: string) {
    const queryClient = useQueryClient();

    useEffect(() => {
        const echo = getEcho();

        if (!echo) {
            return;
        }

        const channel = echo.private(`workflow-runs.${runId}`);

        channel.listen('.run.updated', (event: RunUpdatedEvent) => {
            queryClient.setQueryData(monitoringKeys.detail(runId), event.run);
        });

        channel.listen('.step.updated', (event: StepUpdatedEvent) => {
            queryClient.setQueryData<WorkflowRun | undefined>(
                monitoringKeys.detail(runId),
                (current) => {
                    if (!current) {
                        return current;
                    }

                    const steps = current.steps ?? [];
                    const index = steps.findIndex((step) => step.id === event.step.id);

                    const nextSteps =
                        index === -1
                            ? [...steps, event.step].sort(
                                  (a, b) => (a.execution_order ?? 0) - (b.execution_order ?? 0),
                              )
                            : steps.map((step) => (step.id === event.step.id ? event.step : step));

                    return { ...current, steps: nextSteps };
                },
            );
        });

        return () => {
            channel.stopListening('.run.updated');
            channel.stopListening('.step.updated');
            echo.leave(`workflow-runs.${runId}`);
        };
    }, [queryClient, runId]);
}

export function useTenantRunsRealtime() {
    const queryClient = useQueryClient();
    const tenantId = tenantStorage.getTenantId();

    useEffect(() => {
        const echo = getEcho();

        if (!echo || !tenantId) {
            return;
        }

        const channel = echo.private(`tenants.${tenantId}.workflow-runs`);

        channel.listen('.run.updated', () => {
            queryClient.invalidateQueries({ queryKey: monitoringKeys.lists() });
            queryClient.invalidateQueries({ queryKey: ['monitoring', 'metrics'] });
        });

        return () => {
            channel.stopListening('.run.updated');
            echo.leave(`tenants.${tenantId}.workflow-runs`);
        };
    }, [queryClient, tenantId]);
}
