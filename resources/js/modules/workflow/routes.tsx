import type { RouteObject } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { WorkflowsPage } from '@/modules/workflow/pages/workflows-page';

export const workflowRoutes: RouteObject[] = [
    {
        path: appRoutes.workflow.index,
        element: <WorkflowsPage />,
    },
];
