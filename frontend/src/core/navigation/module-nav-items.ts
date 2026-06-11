import { Activity, Bot, Calendar, GitBranch, LayoutGrid } from 'lucide-react';

import { appRoutes } from '@/core/constants/routes';
import type { NavItem } from '@/types';

export const moduleNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: appRoutes.dashboard,
        icon: LayoutGrid,
    },
    {
        title: 'Workflows',
        href: appRoutes.workflow.index,
        icon: GitBranch,
    },
    {
        title: 'Monitoring',
        href: appRoutes.monitoring.dashboard,
        icon: Activity,
    },
    {
        title: 'Schedules',
        href: appRoutes.scheduler.index,
        icon: Calendar,
    },
    {
        title: 'AI Assistant',
        href: appRoutes.ai.assistant,
        icon: Bot,
    },
];
