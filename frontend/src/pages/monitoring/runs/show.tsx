import { Head } from '@inertiajs/react';

import { RunDetailPage } from '@/modules/monitoring/pages/run-detail-page';

type Props = {
    runId: string;
};

export default function MonitoringRunShow({ runId }: Props) {
    return (
        <>
            <Head title="Workflow Run" />
            <RunDetailPage runId={runId} />
        </>
    );
}

MonitoringRunShow.layout = {
    breadcrumbs: [
        { title: 'Monitoring', href: '/monitoring' },
        { title: 'Run', href: '#' },
    ],
};
