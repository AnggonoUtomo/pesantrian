import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';

export function PrestasiSantriAccessDenied() {
    return (
        <>
            <Head title="Akses Prestasi Santri Ditolak" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Akses ditolak"
                description="Akun ini belum memiliki permission untuk melihat modul Prestasi Santri."
            >
                <section className="dashboard-card dashboard-card--red rounded-2xl border p-5">
                    <h2 className="font-semibold">
                        Permission prestasi_santri.view diperlukan
                    </h2>
                    <p className="mt-2 text-sm text-foreground/65">
                        Hubungi administrator sistem untuk membuka akses modul
                        ini.
                    </p>
                </section>
            </SystemDashboardLayout>
        </>
    );
}
