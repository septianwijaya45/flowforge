import { useMutation } from '@tanstack/react-query';

import { triggerApi } from '@/modules/workflow/api/trigger-api';

export function useRunWorkflow() {
    return useMutation({
        mutationFn: async (workflowId: number) => {
            const { data } = await triggerApi.runManual(workflowId);

            return data.data.run;
        },
    });
}
