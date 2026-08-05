import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import ThemePalette from '@/components/theme-palette';
export default function Appearance() {
    return (
        <>
            <Head title="Appearance settings" />

            <h1 className="sr-only">Appearance settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Appearance settings"
                    description="Update the appearance settings for your account"
                />
                <AppearanceTabs />

                <div className="space-y-3">
                    <div>
                        <h2 className="text-sm font-medium">Theme palette</h2>
                        <p className="text-sm text-muted-foreground">
                            Pilih warna utama untuk tampilan aplikasi.
                        </p>
                    </div>
                    <ThemePalette />
                </div>
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Appearance settings',
            href: '/settings/appearance',
        },
    ],
};
