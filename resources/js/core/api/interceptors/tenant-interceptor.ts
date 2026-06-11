import type { InternalAxiosRequestConfig } from 'axios';

import { tenantStorage } from '@/core/auth/tenant-storage';

export const TENANT_ID_HEADER = 'X-Tenant-Id';

export function attachTenantInterceptor(config: InternalAxiosRequestConfig): InternalAxiosRequestConfig {
    const tenantId = tenantStorage.getTenantId();

    if (tenantId) {
        config.headers[TENANT_ID_HEADER] = tenantId;
    }

    return config;
}
