import type { ReactNode } from 'react';

import { AuthProvider } from '@/app/providers/auth-provider';
import { QueryProvider } from '@/app/providers/query-provider';

interface AppProvidersProps {
    children: ReactNode;
}

export function AppProviders({ children }: AppProvidersProps) {
    return (
        <QueryProvider>
            <AuthProvider>{children}</AuthProvider>
        </QueryProvider>
    );
}
