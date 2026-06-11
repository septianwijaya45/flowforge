import { useParams } from 'react-router-dom';

import { RunDetailPage } from '@/modules/monitoring/pages/run-detail-page';

export function RunDetailRoute() {
    const { runId } = useParams<{ runId: string }>();

    if (!runId) {
        return null;
    }

    return <RunDetailPage runId={runId} />;
}
