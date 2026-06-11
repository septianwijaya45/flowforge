/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    readonly VITE_API_URL: string;
    readonly VITE_AUTH_SERVICE_URL?: string;
    readonly VITE_WORKFLOW_SERVICE_URL?: string;
    readonly VITE_MONITORING_SERVICE_URL?: string;
    readonly VITE_SCHEDULER_SERVICE_URL?: string;
    readonly VITE_AI_SERVICE_URL?: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
