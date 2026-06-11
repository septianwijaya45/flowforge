import { useMutation, useQueryClient } from '@tanstack/react-query';

import { session } from '@/core/auth/session';
import { tenantStorage } from '@/core/auth/tenant-storage';
import { tokenStorage } from '@/core/auth/token-storage';
import { userStorage } from '@/core/auth/user-storage';
import { authApi } from '@/modules/auth/api/auth-api';
import { authKeys } from '@/modules/auth/query-keys';

export function useLogout() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async () => {
            const refreshToken = tokenStorage.getRefreshToken();

            if (refreshToken) {
                await authApi.logout(refreshToken);
            }
        },
        onSettled: () => {
            session.clear();
            userStorage.clear();
            tenantStorage.clear();
            queryClient.removeQueries({ queryKey: authKeys.all });
        },
    });
}
