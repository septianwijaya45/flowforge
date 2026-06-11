import { serviceClients } from '@/core/api/http-client';
import type { AiSuggestion } from '@/modules/ai/types/ai-suggestion';

interface SuggestionsResponse {
    suggestions: AiSuggestion[];
}

export const aiAssistApi = {
    suggest: (prompt: string) =>
        serviceClients.ai.post<SuggestionsResponse>('/ai/suggest', { prompt }),
};
