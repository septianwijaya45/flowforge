import { Head } from '@inertiajs/react';

import { DashboardHomePage } from '@/modules/monitoring/pages/dashboard-home-page';
import { dashboard } from '@/routes';

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <DashboardHomePage />
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
