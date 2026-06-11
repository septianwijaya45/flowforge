import { useMutation } from '@tanstack/react-query';

import { aiWorkflowApi } from '@/modules/ai/api/ai-workflow-api';
import type { BuildWorkflowPayload } from '@/modules/ai/types/generated-workflow';

export function useGenerateWorkflow() {
    return useMutation({
        mutationFn: async (payload: BuildWorkflowPayload) => {
            const { data } = await aiWorkflowApi.build(payload);

            return data.data;
        },
    });
}
