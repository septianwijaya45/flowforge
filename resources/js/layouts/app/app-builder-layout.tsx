import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';

export default function AppBuilderLayout({ children }: { children: React.ReactNode }) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="h-svh min-w-0 overflow-hidden">
                <div className="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                    {children}
                </div>
            </AppContent>
        </AppShell>
    );
}
