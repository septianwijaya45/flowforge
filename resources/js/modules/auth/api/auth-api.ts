import { serviceClients } from '@/core/api/http-client';
import { webClient } from '@/core/api/web-client';
import type { LoginPayload, TokenPair } from '@/modules/auth/types/auth-payload';

interface AuthApiResponse {
    success: boolean;
    message: string;
    data: TokenPair;
}

export const authApi = {
    login: (payload: LoginPayload) =>
        serviceClients.auth.post<AuthApiResponse>('/auth/login', payload),

    logout: (refreshToken: string) =>
        serviceClients.auth.post('/auth/logout', { refresh_token: refreshToken }),

    refresh: (refreshToken: string) =>
        serviceClients.auth.post<AuthApiResponse>('/auth/refresh', {
            refresh_token: refreshToken,
        }),

    sessionToken: () => webClient.post<AuthApiResponse>('/api/v1/auth/session-token'),
};
