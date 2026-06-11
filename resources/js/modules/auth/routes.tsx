import type { RouteObject } from 'react-router-dom';

import { appRoutes } from '@/core/constants/routes';
import { LoginPage } from '@/modules/auth/pages/login-page';

export const authRoutes: RouteObject[] = [
    {
        path: appRoutes.auth.login,
        element: <LoginPage />,
    },
];
