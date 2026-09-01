import { Head } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';

export function SantriAccessDenied() {
    return (
        <>
            <Head title="Data Induk Santri" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Data Induk Santri"
                description="Anda belum memiliki izin untuk melihat data induk santri."
            >
                <div
                    role="alert"
                    className="dashboard-card rounded-2xl border p-6"
                >
                    <ShieldAlert
                        className="mb-3 size-8 text-amber-500"
                        aria-hidden="true"
                    />
                    <h2 className="font-semibold">Akses belum tersedia</h2>
                    <p className="mt-1 text-sm text-foreground/65">
                        Minta permission `santri.view` kepada admin sistem untuk
                        membuka halaman ini.
                    </p>
                </div>
            </SystemDashboardLayout>
        </>
    );
}
