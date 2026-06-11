import { Head } from '@inertiajs/react';

import { MonitoringDashboardPage } from '@/modules/monitoring/pages/monitoring-dashboard-page';

export default function Monitoring() {
    return (
        <>
            <Head title="Monitoring" />
            <MonitoringDashboardPage />
        </>
    );
}

Monitoring.layout = {
    breadcrumbs: [{ title: 'Monitoring', href: '/monitoring' }],
};
