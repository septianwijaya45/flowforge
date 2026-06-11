export const appRoutes = {
    home: '/',
    dashboard: '/dashboard',
    auth: {
        login: '/login',
        register: '/register',
        forgotPassword: '/forgot-password',
    },
    workflow: {
        index: '/workflows',
        detail: (id: string) => `/workflows/${id}`,
        builder: (id: string) => `/workflows/${id}/builder`,
        triggers: (id: string) => `/workflows/${id}/triggers`,
        runs: (id: string) => `/workflows/${id}/runs`,
    },
    monitoring: {
        dashboard: '/monitoring',
        runDetail: (id: string) => `/monitoring/runs/${id}`,
    },
    scheduler: {
        index: '/schedules',
        detail: (id: string) => `/schedules/${id}`,
    },
    ai: {
        assistant: '/ai/assistant',
    },
} as const;
