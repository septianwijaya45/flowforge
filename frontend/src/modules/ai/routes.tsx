import { Navigate } from 'react-router-dom';
import type { RouteObject } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { AiWorkflowGeneratorPage } from '@/modules/ai/pages/ai-workflow-generator-page';

export const aiRoutes: RouteObject[] = [
    {
        path: appRoutes.ai.generator,
        element: <AiWorkflowGeneratorPage />,
    },
    {
        path: appRoutes.ai.assistant,
        element: <Navigate to={appRoutes.ai.generator} replace />,
    },
];
