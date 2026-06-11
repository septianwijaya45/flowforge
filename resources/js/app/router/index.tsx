import { createBrowserRouter } from 'react-router-dom';

import { RootRoute } from '@/app/router/root-route';
import { RequireAuth } from '@/app/router/route-guards/require-auth';
import { ShellLayout } from '@/app/router/shell-layout';
import { aiRoutes } from '@/modules/ai';
import { authRoutes } from '@/modules/auth';
import { monitoringRoutes } from '@/modules/monitoring';
import { schedulerRoutes } from '@/modules/scheduler';
import { workflowRoutes } from '@/modules/workflow';

export const router = createBrowserRouter([
    {
        element: <RootRoute />,
        children: [
            ...authRoutes,
            {
                element: <RequireAuth />,
                children: [
                    {
                        element: <ShellLayout />,
                        children: [...workflowRoutes, ...monitoringRoutes, ...schedulerRoutes, ...aiRoutes],
                    },
                ],
            },
        ],
    },
]);
