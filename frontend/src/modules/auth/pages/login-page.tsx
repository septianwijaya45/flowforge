import { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { toast } from 'sonner';

import { useAuthState } from '@/app/providers/auth-provider';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { tenantStorage } from '@/core/auth/tenant-storage';
import { userStorage } from '@/core/auth/user-storage';
import { appRoutes } from '@/core/constants/routes';
import { AuthLayout } from '@/layouts/auth-layout';
import { useLogin } from '@/modules/auth/hooks/use-login';
import type { User as ApiUser } from '@/modules/auth/types/user';
import type { User } from '@/types';

const defaultTenantId = import.meta.env.VITE_DEFAULT_TENANT_ID as string | undefined;

function toAuthUser(user: ApiUser): User {
    return {
        id: 0,
        name: user.name,
        email: user.email,
        role: user.role,
        email_verified_at: null,
        created_at: '',
        updated_at: '',
    };
}

export function LoginPage() {
    const navigate = useNavigate();
    const location = useLocation();
    const { setUser, setApiAuthReady } = useAuthState();
    const login = useLogin();

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [errors, setErrors] = useState<{ email?: string; password?: string }>({});

    const redirectTo =
        (location.state as { from?: { pathname?: string } } | null)?.from?.pathname ??
        appRoutes.dashboard;

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        setErrors({});

        login.mutate(
            { email, password },
            {
                onSuccess: (tokens) => {
                    userStorage.setUser(tokens.user);

                    if (defaultTenantId) {
                        tenantStorage.setTenantId(defaultTenantId);
                    }

                    setUser(toAuthUser(tokens.user));
                    setApiAuthReady(true);

                    toast.success('Signed in successfully');
                    navigate(redirectTo, { replace: true });
                },
                onError: (error) => {
                    const message = error.message || 'Invalid email or password.';

                    setErrors({ email: message });
                    toast.error('Sign in failed', { description: message });
                },
            },
        );
    };

    return (
        <AuthLayout
            title="Log in to your account"
            description="Enter your email and password below to log in"
        >
            <form onSubmit={handleSubmit} className="flex flex-col gap-6">
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            value={email}
                            onChange={(event) => setEmail(event.target.value)}
                            required
                            autoFocus
                            autoComplete="email"
                            placeholder="admin@flowforge.test"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <PasswordInput
                            id="password"
                            value={password}
                            onChange={(event) => setPassword(event.target.value)}
                            required
                            autoComplete="current-password"
                            placeholder="Password"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <Button
                        type="submit"
                        className="mt-2 w-full"
                        disabled={login.isPending}
                        data-test="login-button"
                    >
                        {login.isPending && <Spinner />}
                        Log in
                    </Button>
                </div>

                <p className="text-center text-sm text-muted-foreground">
                    Demo after seeding:{' '}
                    <span className="font-medium text-foreground">admin@flowforge.test</span> /{' '}
                    <span className="font-medium text-foreground">password</span>
                </p>

                <p className="text-center text-sm text-muted-foreground">
                    Open the app home at{' '}
                    <Link to={appRoutes.dashboard} className="text-primary hover:underline">
                        Dashboard
                    </Link>
                </p>
            </form>
        </AuthLayout>
    );
}
