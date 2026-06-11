import type { RouteObject } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { DashboardHomePage } from '@/modules/monitoring/pages/dashboard-home-page';
import { MonitoringDashboardPage } from '@/modules/monitoring/pages/monitoring-dashboard-page';
import { RunDetailRoute } from '@/modules/monitoring/pages/run-detail-route';

export const monitoringRoutes: RouteObject[] = [
    {
        path: appRoutes.dashboard,
        element: <DashboardHomePage />,
    },
    {
        path: appRoutes.monitoring.dashboard,
        element: <MonitoringDashboardPage />,
    },
    {
        path: appRoutes.monitoring.runDetail(':runId'),
        element: <RunDetailRoute />,
    },
];
