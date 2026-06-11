import '@/css/app.css';

import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';

import { AppProviders } from '@/app/providers/app-providers';
import { router } from '@/app/router';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';

initializeTheme();

const root = document.getElementById('root');

if (root) {
    createRoot(root).render(
        <StrictMode>
            <AppProviders>
                <TooltipProvider delayDuration={0}>
                    <RouterProvider router={router} />
                    <Toaster />
                </TooltipProvider>
            </AppProviders>
        </StrictMode>,
    );
}
