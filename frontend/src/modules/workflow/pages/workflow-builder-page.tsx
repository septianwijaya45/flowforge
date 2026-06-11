import { toast } from 'sonner';

import { useAuth } from '@/app/providers/auth-provider';
import { Skeleton } from '@/components/ui/skeleton';
import { appRoutes } from '@/core/constants/routes';
import { canWrite } from '@/core/auth/permissions';
import { WorkflowCanvas } from '@/modules/workflow/components/builder/workflow-canvas';
import { useRunWorkflow } from '@/modules/workflow/hooks/use-run-workflow';
import { useSaveWorkflowVersion } from '@/modules/workflow/hooks/use-save-workflow-version';
import { useWorkflowBuilder } from '@/modules/workflow/hooks/use-workflow-builder';
import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';

interface WorkflowBuilderPageProps {
    workflowId: string;
}

export function WorkflowBuilderPage({ workflowId }: WorkflowBuilderPageProps) {
    const { user } = useAuth();
    const userCanWrite = canWrite(user?.role as string | undefined);
    const { workflow, version, initialDefinition, isLoading, isError, error } =
        useWorkflowBuilder(workflowId);
    const saveVersion = useSaveWorkflowVersion();
    const runWorkflow = useRunWorkflow();

    const triggerRun = () => {
        runWorkflow.mutate(workflowId, {
            onSuccess: (run) => {
                toast.success(`Workflow "${workflow?.name}" started`, {
                    description: `Run ${run.id} is ${run.status}.`,
                    action: {
                        label: 'Monitor',
                        onClick: () => {
                            window.location.href = appRoutes.monitoring.runDetail(run.id);
                        },
                    },
                });
            },
            onError: (runError) => {
                toast.error('Failed to run workflow', {
                    description: runError.message,
                });
            },
        });
    };

    const handleRun = (definition: WorkflowDefinition, needsSave: boolean) => {
        if (!userCanWrite) {
            return;
        }

        if (saveVersion.isPending || runWorkflow.isPending) {
            return;
        }

        const shouldSave = needsSave || workflow?.current_version_id === null;

        if (!shouldSave) {
            triggerRun();

            return;
        }

        saveVersion.mutate(
            {
                workflowId,
                definition,
                changeSummary: 'Updated via workflow builder before run',
            },
            {
                onSuccess: () => {
                    triggerRun();
                },
                onError: (saveError) => {
                    toast.error('Failed to save workflow before run', {
                        description: saveError.message,
                    });
                },
            },
        );
    };

    if (isLoading) {
        return (
            <div className="flex min-h-0 flex-1 flex-col gap-4 p-4">
                <Skeleton className="h-10 w-64 shrink-0" />
                <Skeleton className="min-h-0 flex-1" />
            </div>
        );
    }

    if (isError) {
        return (
            <p className="m-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                {error?.message ?? 'Failed to load workflow builder.'}
            </p>
        );
    }

    if (!workflow || !initialDefinition) {
        return null;
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col overflow-hidden">
            <WorkflowCanvas
                key={version?.id ?? 'draft'}
                workflow={workflow}
                initialDefinition={initialDefinition}
                versionNumber={version?.version_number}
                isSaving={saveVersion.isPending}
                isRunning={runWorkflow.isPending}
                canRun={userCanWrite}
                onValidationError={(message) =>
                    toast.error('Validation failed', { description: message })
                }
                onSave={(definition) => {
                    saveVersion.mutate(
                        {
                            workflowId,
                            definition,
                            changeSummary: 'Updated via workflow builder',
                        },
                        {
                            onSuccess: (savedVersion) => {
                                toast.success('Workflow saved', {
                                    description: `Version ${savedVersion.version_number} created.`,
                                });
                            },
                            onError: (saveError) => {
                                toast.error('Failed to save workflow', {
                                    description: saveError.message,
                                });
                            },
                        },
                    );
                }}
                onRun={userCanWrite ? handleRun : undefined}
            />
        </div>
    );
}
