import { Head } from '@inertiajs/react';

import { DashboardOverview } from '@/modules/monitoring/components/dashboard-overview';
import { dashboard } from '@/routes';

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <DashboardOverview />
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
