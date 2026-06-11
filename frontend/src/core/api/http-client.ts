import axios from 'axios';
import type {AxiosInstance} from 'axios';

import { serviceEndpoints } from '@/core/api/endpoints';
import { attachAuthInterceptor } from '@/core/api/interceptors/auth-interceptor';
import { normalizeApiError } from '@/core/api/interceptors/error-interceptor';
import { attachTenantInterceptor } from '@/core/api/interceptors/tenant-interceptor';

export function createHttpClient(baseURL: string): AxiosInstance {
    const client = axios.create({
        baseURL,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        withCredentials: true,
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    });

    client.interceptors.request.use((config) => {
        attachAuthInterceptor(config);
        attachTenantInterceptor(config);

        return config;
    });

    client.interceptors.response.use(
        (response) => response,
        (error) => normalizeApiError(error),
    );

    return client;
}

/** Default client for monolith — all modules share one host until services split. */
export const httpClient = createHttpClient(serviceEndpoints.auth);

/** Per-service clients for future microservice extraction. */
export const serviceClients = {
    auth: createHttpClient(serviceEndpoints.auth),
    workflow: createHttpClient(serviceEndpoints.workflow),
    monitoring: createHttpClient(serviceEndpoints.monitoring),
    scheduler: createHttpClient(serviceEndpoints.scheduler),
    ai: createHttpClient(serviceEndpoints.ai),
} as const;
