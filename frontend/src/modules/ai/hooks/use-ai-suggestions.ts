import { useMutation } from '@tanstack/react-query';

import { aiAssistApi } from '@/modules/ai/api/ai-assist-api';

export function useAiSuggestions() {
    return useMutation({
        mutationFn: async (prompt: string) => {
            const { data } = await aiAssistApi.suggest(prompt);

            return data.suggestions;
        },
    });
}
