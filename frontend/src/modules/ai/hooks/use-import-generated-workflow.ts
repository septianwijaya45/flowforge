import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { applyDefinitionLayout } from '@/modules/ai/utils/apply-definition-layout';
import { versionApi } from '@/modules/workflow/api/version-api';
import { workflowApi } from '@/modules/workflow/api/workflow-api';
import { workflowKeys } from '@/modules/workflow/query-keys';
import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';

interface ImportGeneratedWorkflowInput {
    name: string;
    description?: string;
    definition: WorkflowDefinition;
    existingWorkflowId?: string;
}

export function useImportGeneratedWorkflow() {
    const navigate = useNavigate();
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({
            name,
            description,
            definition,
            existingWorkflowId,
        }: ImportGeneratedWorkflowInput) => {
            const layoutDefinition = applyDefinitionLayout(definition);

            let workflowId = existingWorkflowId;

            if (!workflowId) {
                const { data } = await workflowApi.create({
                    name,
                    description: description ?? null,
                });

                workflowId = data.data.workflow.id;
            }

            await versionApi.saveVersion(
                workflowId,
                layoutDefinition,
                'Imported from AI workflow generator',
            );

            return workflowId;
        },
        onSuccess: (workflowId) => {
            queryClient.invalidateQueries({ queryKey: workflowKeys.lists() });
            queryClient.invalidateQueries({ queryKey: workflowKeys.detail(workflowId) });
            queryClient.invalidateQueries({
                queryKey: [...workflowKeys.detail(workflowId), 'current-version'],
            });

            navigate(appRoutes.workflow.builder(workflowId));
        },
    });
}
