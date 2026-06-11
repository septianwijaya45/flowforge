import { usePage } from '@inertiajs/react';
import { useEffect, useLayoutEffect } from 'react';

import { useAuthState } from '@/app/providers/auth-provider';
import { tenantStorage } from '@/core/auth/tenant-storage';
import { tokenStorage } from '@/core/auth/token-storage';
import { authApi } from '@/modules/auth/api/auth-api';
import type { User } from '@/types';

interface SharedProps {
    auth?: { user: User | null };
    tenant?: { id: string; name: string; slug: string } | null;
}

export function SessionBridge() {
    const { setApiAuthReady } = useAuthState();
    const { props } = usePage<{ auth?: SharedProps['auth']; tenant?: SharedProps['tenant'] }>();
    const user = props.auth?.user;
    const tenant = props.tenant;

    useLayoutEffect(() => {
        if (tenant?.id) {
            tenantStorage.setTenantId(tenant.id);

            return;
        }

        tenantStorage.clear();
    }, [tenant?.id]);

    useEffect(() => {
        if (!user) {
            tokenStorage.clear();
            setApiAuthReady(false);

            return;
        }

        let cancelled = false;

        setApiAuthReady(false);

        authApi
            .sessionToken()
            .then(({ data }) => {
                if (cancelled) {
                    return;
                }

                tokenStorage.setTokens(data.data.access_token, data.data.refresh_token);
                setApiAuthReady(true);
            })
            .catch(() => {
                if (cancelled) {
                    return;
                }

                tokenStorage.clear();
                setApiAuthReady(false);
            });

        return () => {
            cancelled = true;
        };
    }, [user, setApiAuthReady]);

    return null;
}
