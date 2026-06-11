import { useMutation } from '@tanstack/react-query';

import { tokenStorage } from '@/core/auth/token-storage';
import { authApi } from '@/modules/auth/api/auth-api';
import type { LoginPayload } from '@/modules/auth/types/auth-payload';

export function useLogin() {
    return useMutation({
        mutationFn: async (payload: LoginPayload) => {
            const { data } = await authApi.login(payload);
            const tokens = data.data;

            tokenStorage.setTokens(tokens.access_token, tokens.refresh_token);

            return tokens;
        },
    });
}
