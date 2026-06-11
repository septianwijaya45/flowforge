import { serviceClients } from '@/core/api/http-client';
import type {
    BuildWorkflowPayload,
    GeneratedWorkflowResult,
} from '@/modules/ai/types/generated-workflow';

interface BuildWorkflowResponse {
    success: boolean;
    message: string;
    data: GeneratedWorkflowResult;
}

export const aiWorkflowApi = {
    build: (payload: BuildWorkflowPayload) =>
        serviceClients.ai.post<BuildWorkflowResponse>('/ai/workflows/build', payload),
};
