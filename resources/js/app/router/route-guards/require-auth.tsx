import { Navigate, Outlet, useLocation } from 'react-router-dom';

import { session } from '@/core/auth/session';
import { appRoutes } from '@/core/constants/routes';

export function RequireAuth() {
    const location = useLocation();

    if (!session.isAuthenticated()) {
        return <Navigate to={appRoutes.auth.login} state={{ from: location }} replace />;
    }

    return <Outlet />;
}
