import type { ReactNode } from 'react';

import { AuthProvider } from '@/app/providers/auth-provider';
import { QueryProvider } from '@/app/providers/query-provider';
import { SpaSessionBridge } from '@/app/providers/spa-session-bridge';

interface AppProvidersProps {
    children: ReactNode;
}

export function AppProviders({ children }: AppProvidersProps) {
    return (
        <QueryProvider>
            <AuthProvider>
                <SpaSessionBridge />
                {children}
            </AuthProvider>
        </QueryProvider>
    );
}
