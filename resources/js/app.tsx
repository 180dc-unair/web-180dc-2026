import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { TooltipProvider } from '@/components/ui/tooltip';

const appName = import.meta.env.VITE_APP_NAME || '180DC Uniar';

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 1000 * 30,
            retry: 1,
            refetchOnWindowFocus: false,
        },
    },
});

const pages = import.meta.glob<{ default: React.ComponentType }>('./Pages/**/*.tsx');

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        const loadPage = pages[`./Pages/${name}.tsx`];

        if (!loadPage) {
            throw new Error(`Halaman Inertia tidak ditemukan: ${name}`);
        }

        return (await loadPage()).default;
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <React.StrictMode>
                <QueryClientProvider client={queryClient}>
                    <TooltipProvider>
                        <App {...props} />
                    </TooltipProvider>
                </QueryClientProvider>
            </React.StrictMode>,
        );
    },
    progress: {
        color: '#72BB0E',
    },
});
