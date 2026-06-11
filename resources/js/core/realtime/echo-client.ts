import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

import { tenantStorage } from '@/core/auth/tenant-storage';
import { tokenStorage } from '@/core/auth/token-storage';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo?: Echo<'reverb'>;
    }
}

let echoInstance: Echo<'reverb'> | null = null;

export function getEcho(): Echo<'reverb'> | null {
    const key = import.meta.env.VITE_REVERB_APP_KEY;

    if (!key) {
        return null;
    }

    if (!echoInstance) {
        window.Pusher = Pusher;

        const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
        const host = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
        const port = Number(import.meta.env.VITE_REVERB_PORT ?? (scheme === 'https' ? 443 : 8080));

        const authHeaders: Record<string, string> = {
            Accept: 'application/json',
        };

        const token = tokenStorage.getAccessToken();
        const tenantId = tenantStorage.getTenantId();

        if (token) {
            authHeaders.Authorization = `Bearer ${token}`;
        }

        if (tenantId) {
            authHeaders['X-Tenant-Id'] = tenantId;
        }

        echoInstance = new Echo({
            broadcaster: 'reverb',
            key,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: authHeaders,
            },
        });

        window.Echo = echoInstance;
    }

    return echoInstance;
}

export function disconnectEcho(): void {
    echoInstance?.disconnect();
    echoInstance = null;
    window.Echo = undefined;
}
