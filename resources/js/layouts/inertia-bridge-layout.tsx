import type { ReactNode } from 'react';

import { InertiaAuthSync } from '@/app/providers/inertia-auth-sync';
import { SessionBridge } from '@/app/providers/session-bridge';

export default function InertiaBridgeLayout({ children }: { children: ReactNode }) {
    return (
        <>
            <InertiaAuthSync />
            <SessionBridge />
            {children}
        </>
    );
}
