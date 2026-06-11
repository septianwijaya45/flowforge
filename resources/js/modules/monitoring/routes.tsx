import type { RouteObject } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { MonitoringDashboardPage } from '@/modules/monitoring/pages/monitoring-dashboard-page';

export const monitoringRoutes: RouteObject[] = [
    {
        path: appRoutes.monitoring.dashboard,
        element: <MonitoringDashboardPage />,
    },
];
