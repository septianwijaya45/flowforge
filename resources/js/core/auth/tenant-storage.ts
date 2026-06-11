const TENANT_ID_KEY = 'flowforge_tenant_id';

export const tenantStorage = {
    getTenantId(): string | null {
        return localStorage.getItem(TENANT_ID_KEY);
    },

    setTenantId(tenantId: string): void {
        localStorage.setItem(TENANT_ID_KEY, tenantId);
    },

    clear(): void {
        localStorage.removeItem(TENANT_ID_KEY);
    },
};
