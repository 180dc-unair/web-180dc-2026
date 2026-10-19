import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';

const vendorChunks = {
    react: ['react', 'react-dom'],
    inertia: ['@inertiajs/react'],
    tanstack: ['@tanstack/react-query'],
    icons: ['lucide-react'],
    radix: [
        '@radix-ui/react-dialog',
        '@radix-ui/react-select',
        '@radix-ui/react-alert-dialog',
        '@radix-ui/react-dropdown-menu',
        '@radix-ui/react-tooltip',
    ],
};

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        react(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return undefined;
                    }
                    for (const [chunk, modules] of Object.entries(vendorChunks)) {
                        if (modules.some((m) => id.includes(`node_modules/${m}/`) || id.includes(`node_modules/${m}`))) {
                            return chunk;
                        }
                    }
                    return 'vendor';
                },
            },
        },
    },
});
