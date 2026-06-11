import { toast } from 'sonner';

import { Skeleton } from '@/components/ui/skeleton';
import { WorkflowCanvas } from '@/modules/workflow/components/builder/workflow-canvas';
import { useSaveWorkflowVersion } from '@/modules/workflow/hooks/use-save-workflow-version';
import { useWorkflowBuilder } from '@/modules/workflow/hooks/use-workflow-builder';

interface WorkflowBuilderPageProps {
    workflowId: string;
}

export function WorkflowBuilderPage({ workflowId }: WorkflowBuilderPageProps) {
    const { workflow, version, initialDefinition, isLoading, isError, error } =
        useWorkflowBuilder(workflowId);
    const saveVersion = useSaveWorkflowVersion();

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
            />
        </div>
    );
}
