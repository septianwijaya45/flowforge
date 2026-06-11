import type { RouteObject } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { AiAssistantPage } from '@/modules/ai/pages/ai-assistant-page';

export const aiRoutes: RouteObject[] = [
    {
        path: appRoutes.ai.assistant,
        element: <AiAssistantPage />,
    },
];
