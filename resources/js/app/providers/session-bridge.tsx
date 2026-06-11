import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

import { tenantStorage } from '@/core/auth/tenant-storage';
import { tokenStorage } from '@/core/auth/token-storage';
import { authApi } from '@/modules/auth/api/auth-api';
import type { User } from '@/types';

interface SharedProps {
    auth?: { user: User | null };
    tenant?: { id: string; name: string; slug: string } | null;
}

export function SessionBridge() {
    const { props } = usePage<{ auth?: SharedProps['auth']; tenant?: SharedProps['tenant'] }>();
    const user = props.auth?.user;
    const tenant = props.tenant;

    useEffect(() => {
        if (tenant?.id) {
            tenantStorage.setTenantId(tenant.id);
        }
    }, [tenant?.id]);

    useEffect(() => {
        if (!user || tokenStorage.getAccessToken()) {
            return;
        }

        authApi.sessionToken().then(({ data }) => {
            const tokens = data.data;
            tokenStorage.setTokens(tokens.access_token, tokens.refresh_token);
        });
    }, [user]);

    return null;
}
