import { createInertiaApp } from '@inertiajs/react';
import type { ResolvedComponent } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import { initializeThemePalette } from '@/hooks/use-theme-palette';
import { initializeTypography } from '@/hooks/use-typography';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { setZiggy } from '@/lib/ziggy';

const appName =
    (typeof document !== 'undefined' &&
        document.documentElement.dataset.appName) ||
    import.meta.env.VITE_APP_NAME ||
    'Laravel';

createInertiaApp({
    resolve: async (name): Promise<ResolvedComponent> => {
        const pages = import.meta.glob([
            './pages/**/*.tsx',
            './modules/**/*.tsx',
        ]) as Record<string, () => Promise<{ default: ResolvedComponent }>>;
        const page = pages[`./pages/${name}.tsx`] || pages[`./${name}.tsx`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        const module = await page();

        return 'default' in module ? module.default : module;
    },

    title: (title) => (title ? `${title} - ${appName}` : appName),

    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;

            case name.startsWith('auth/'):
                return AuthLayout;

            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];

            default:
                return AppLayout;
        }
    },

    strictMode: true,

    withApp(app, { page }) {
        setZiggy(page.props.ziggy);

        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },

    progress: {
        color: '#4B5563',
    },
});

if (typeof window !== 'undefined') {
    initializeTheme();
    initializeThemePalette();
    initializeTypography();
}
