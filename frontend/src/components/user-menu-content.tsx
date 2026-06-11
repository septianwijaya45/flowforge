import { LogOut } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

import {
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { appRoutes } from '@/core/constants/routes';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { useLogout } from '@/modules/auth/hooks/use-logout';
import type { User } from '@/types';

type Props = {
    user: User;
};

export function UserMenuContent({ user }: Props) {
    const cleanup = useMobileNavigation();
    const navigate = useNavigate();
    const logoutMutation = useLogout();

    const handleLogout = async () => {
        cleanup();

        try {
            await logoutMutation.mutateAsync();
        } finally {
            navigate(appRoutes.auth.login, { replace: true });
        }
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                onClick={() => void handleLogout()}
                disabled={logoutMutation.isPending}
                data-test="logout-button"
            >
                <LogOut className="mr-2" />
                Log out
            </DropdownMenuItem>
        </>
    );
}
