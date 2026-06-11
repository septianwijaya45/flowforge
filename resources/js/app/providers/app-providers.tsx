import type { ReactNode } from 'react';

import { AuthProvider } from '@/app/providers/auth-provider';
import { InertiaAuthSync } from '@/app/providers/inertia-auth-sync';
import { QueryProvider } from '@/app/providers/query-provider';
import { SessionBridge } from '@/app/providers/session-bridge';

interface AppProvidersProps {
    children: ReactNode;
    /** Enable Inertia session → JWT bridge (monolith entry only). */
    inertia?: boolean;
}

export function AppProviders({ children, inertia = false }: AppProvidersProps) {
    return (
        <QueryProvider>
            <AuthProvider>
                {inertia ? (
                    <>
                        <InertiaAuthSync />
                        <SessionBridge />
                    </>
                ) : null}
                {children}
            </AuthProvider>
        </QueryProvider>
    );
}
