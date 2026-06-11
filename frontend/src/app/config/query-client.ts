import { QueryClient } from '@tanstack/react-query';

import { queryStaleTimes } from '@/core/constants/query-stale-times';

export const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: queryStaleTimes.default,
            retry: 1,
            refetchOnWindowFocus: false,
        },
    },
});
