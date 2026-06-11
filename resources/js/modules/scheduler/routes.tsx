import type { RouteObject } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { SchedulesPage } from '@/modules/scheduler/pages/schedules-page';

export const schedulerRoutes: RouteObject[] = [
    {
        path: appRoutes.scheduler.index,
        element: <SchedulesPage />,
    },
];
