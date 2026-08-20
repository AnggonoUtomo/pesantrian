import { fileURLToPath, URL } from 'node:url';
import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [react()],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    test: {
        environment: 'jsdom',
        setupFiles: ['./resources/js/test/setup.ts'],
        include: [
            './resources/js/**/*.test.{ts,tsx}',
            './tests/Frontend/**/*.test.{ts,tsx}',
        ],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json-summary', 'lcov'],
            reportsDirectory: './build/coverage/frontend',
            include: [
                'resources/js/lib/authorization.ts',
                'resources/js/components/input-error.tsx',
                'resources/js/components/ui/loading-button.tsx',
                'resources/js/pages/System/AccessControl/components/RoleControlCard.tsx',
            ],
            thresholds: {
                branches: 80,
                functions: 80,
                lines: 80,
                statements: 80,
            },
        },
    },
});
