import { createInertiaApp } from '@inertiajs/react';
import { AppProviders } from '@/app/providers/app-providers';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AppBuilderLayout from '@/layouts/app/app-builder-layout';
import AuthLayout from '@/layouts/auth-layout';
import InertiaBridgeLayout from '@/layouts/inertia-bridge-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function resolvePageLayout(name: string) {
    switch (true) {
        case name === 'welcome':
            return null;
        case name === 'workflows/builder':
            return AppBuilderLayout;
        case name.startsWith('auth/'):
            return AuthLayout;
        case name.startsWith('settings/'):
            return [AppLayout, SettingsLayout];
        default:
            return AppLayout;
    }
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        const pageLayout = resolvePageLayout(name);

        if (pageLayout === null) {
            return InertiaBridgeLayout;
        }

        if (Array.isArray(pageLayout)) {
            return [InertiaBridgeLayout, ...pageLayout];
        }

        return [InertiaBridgeLayout, pageLayout];
    },
    strictMode: true,
    withApp(app) {
        return (
            <AppProviders>
                <TooltipProvider delayDuration={0}>
                    {app}
                    <Toaster />
                </TooltipProvider>
            </AppProviders>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
