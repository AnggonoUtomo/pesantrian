import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        inertia({
            ssr: { sourcemap: false },
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    const nodeModulesIndex = id.lastIndexOf('node_modules/');

                    if (nodeModulesIndex !== -1) {
                        const packagePath = id.slice(
                            nodeModulesIndex + 'node_modules/'.length,
                        );
                        const packageParts = packagePath.split('/');
                        const packageName = packagePath.startsWith('@')
                            ? packageParts.slice(0, 2).join('-')
                            : packageParts[0];

                        return `vendor-${packageName.replaceAll('@', '')}`;
                    }
                },
            },
        },
    },
});
