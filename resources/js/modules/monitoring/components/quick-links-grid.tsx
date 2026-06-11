import { Link } from '@inertiajs/react';
import { Activity, Calendar, ChevronRight, GitBranch } from 'lucide-react';

import { appRoutes } from '@/core/constants/routes';

const links = [
    {
        title: 'Workflows',
        description: 'Design and manage automation flows.',
        href: appRoutes.workflow.index,
        icon: GitBranch,
    },
    {
        title: 'Monitoring',
        description: 'Inspect runs, charts, and live execution status.',
        href: appRoutes.monitoring.dashboard,
        icon: Activity,
    },
    {
        title: 'Schedules',
        description: 'Configure cron-based workflow triggers.',
        href: appRoutes.scheduler.index,
        icon: Calendar,
    },
] as const;

export function QuickLinksGrid() {
    return (
        <section className="space-y-3">
            <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                Quick links
            </h2>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {links.map((link) => {
                    const Icon = link.icon;

                    return (
                        <Link
                            key={link.href}
                            href={link.href}
                            className="group flex items-start gap-3 rounded-lg border bg-card p-4 shadow-xs transition hover:border-primary/40"
                        >
                            <div className="rounded-md bg-muted p-2 text-primary">
                                <Icon className="size-4" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="font-medium">{link.title}</p>
                                <p className="mt-1 text-sm text-muted-foreground">{link.description}</p>
                            </div>
                            <ChevronRight className="mt-0.5 size-4 shrink-0 text-muted-foreground transition group-hover:text-primary" />
                        </Link>
                    );
                })}
            </div>
        </section>
    );
}
