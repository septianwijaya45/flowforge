import { Head } from '@inertiajs/react';

import { SchedulesPage } from '@/modules/scheduler/pages/schedules-page';

export default function Schedules() {
    return (
        <>
            <Head title="Schedules" />
            <SchedulesPage />
        </>
    );
}

Schedules.layout = {
    breadcrumbs: [{ title: 'Schedules', href: '/schedules' }],
};
