import type { InternalAxiosRequestConfig } from 'axios';

import { tokenStorage } from '@/core/auth/token-storage';

export function attachAuthInterceptor(config: InternalAxiosRequestConfig): InternalAxiosRequestConfig {
    const token = tokenStorage.getAccessToken();

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
}
