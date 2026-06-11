/**
 * Service base URLs. In the monolith every value defaults to the same API host.
 * When extracting microservices, override each via VITE_*_SERVICE_URL.
 */
const defaultBase = import.meta.env.VITE_API_URL ?? '/api/v1';

export const serviceEndpoints = {
    auth: import.meta.env.VITE_AUTH_SERVICE_URL ?? defaultBase,
    workflow: import.meta.env.VITE_WORKFLOW_SERVICE_URL ?? defaultBase,
    monitoring: import.meta.env.VITE_MONITORING_SERVICE_URL ?? defaultBase,
    scheduler: import.meta.env.VITE_SCHEDULER_SERVICE_URL ?? defaultBase,
    ai: import.meta.env.VITE_AI_SERVICE_URL ?? defaultBase,
} as const;

export type ServiceName = keyof typeof serviceEndpoints;
