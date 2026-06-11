function requireEnv(key: keyof ImportMetaEnv, fallback?: string): string {
    const value = import.meta.env[key] ?? fallback;

    if (!value) {
        throw new Error(`Missing environment variable: ${key}`);
    }

    return value;
}

export const env = {
    appName: import.meta.env.VITE_APP_NAME ?? 'FlowForge',
    apiUrl: requireEnv('VITE_API_URL', '/api/v1'),
} as const;
