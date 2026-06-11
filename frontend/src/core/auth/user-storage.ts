import type { UserRole } from '@/modules/auth/types/user';

const USER_KEY = 'flowforge_user';

export interface StoredUser {
    uuid: string;
    name: string;
    email: string;
    role: UserRole;
}

export const userStorage = {
    getUser(): StoredUser | null {
        const raw = localStorage.getItem(USER_KEY);

        if (!raw) {
            return null;
        }

        try {
            return JSON.parse(raw) as StoredUser;
        } catch {
            return null;
        }
    },

    setUser(user: StoredUser): void {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    },

    clear(): void {
        localStorage.removeItem(USER_KEY);
    },
};
