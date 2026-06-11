import { Outlet } from 'react-router-dom';

import AppLayout from '@/layouts/app-layout';

export function ShellLayout() {
    return (
        <AppLayout>
            <Outlet />
        </AppLayout>
    );
}
