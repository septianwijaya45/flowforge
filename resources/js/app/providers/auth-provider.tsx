import { createContext, useContext, useMemo, useState } from 'react';
import type { Dispatch, ReactNode, SetStateAction } from 'react';

import { session } from '@/core/auth/session';
import type { User } from '@/types';

interface AuthContextValue {
    user: User | null;
    isAuthenticated: boolean;
}

interface AuthContextState {
    value: AuthContextValue;
    setUser: Dispatch<SetStateAction<User | null>>;
}

const AuthContext = createContext<AuthContextState | null>(null);

interface AuthProviderProps {
    children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
    const [user, setUser] = useState<User | null>(null);

    const value = useMemo<AuthContextValue>(
        () => ({
            user,
            isAuthenticated: user !== null || session.isAuthenticated(),
        }),
        [user],
    );

    const state = useMemo<AuthContextState>(
        () => ({ value, setUser }),
        [value],
    );

    return <AuthContext.Provider value={state}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth must be used within AuthProvider');
    }

    return context.value;
}

export function useAuthState(): AuthContextState {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuthState must be used within AuthProvider');
    }

    return context;
}
