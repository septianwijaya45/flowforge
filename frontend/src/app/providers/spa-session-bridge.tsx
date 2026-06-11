import { useEffect } from 'react';

import { useAuthState } from '@/app/providers/auth-provider';
import { tenantStorage } from '@/core/auth/tenant-storage';
import { tokenStorage } from '@/core/auth/token-storage';
import { userStorage } from '@/core/auth/user-storage';
import type { User } from '@/types';

const defaultTenantId = import.meta.env.VITE_DEFAULT_TENANT_ID as string | undefined;

function toAuthUser(stored: ReturnType<typeof userStorage.getUser>): User | null {
    if (!stored) {
        return null;
    }

    return {
        id: 0,
        name: stored.name,
        email: stored.email,
        role: stored.role,
        email_verified_at: null,
        created_at: '',
        updated_at: '',
    };
}

export function SpaSessionBridge() {
    const { setUser, setApiAuthReady } = useAuthState();

    useEffect(() => {
        const accessToken = tokenStorage.getAccessToken();

        if (!accessToken) {
            setUser(null);
            setApiAuthReady(false);

            return;
        }

        const storedUser = userStorage.getUser();

        if (storedUser) {
            setUser(toAuthUser(storedUser));
        }

        if (defaultTenantId) {
            tenantStorage.setTenantId(defaultTenantId);
        }

        setApiAuthReady(true);
    }, [setApiAuthReady, setUser]);

    return null;
}
