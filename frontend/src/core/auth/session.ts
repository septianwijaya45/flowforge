import { tokenStorage } from '@/core/auth/token-storage';

export const session = {
    isAuthenticated(): boolean {
        return tokenStorage.getAccessToken() !== null;
    },

    clear(): void {
        tokenStorage.clear();
    },
};
