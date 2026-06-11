import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

import { useAuthState } from '@/app/providers/auth-provider';
import type { User } from '@/types';

export function InertiaAuthSync() {
    const { setUser } = useAuthState();
    const { props } = usePage<{ auth?: { user: User | null } }>();
    const user = props.auth?.user ?? null;

    useEffect(() => {
        setUser(user);
    }, [user, setUser]);

    return null;
}
