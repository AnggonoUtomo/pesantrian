import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';

export function KedisiplinanSantriAccessDenied() {
    return (
        <>
            <Head title="Akses Kedisiplinan Santri Ditolak" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Akses ditolak"
                description="Akun ini belum memiliki permission untuk melihat modul Pelanggaran / Kedisiplinan."
            >
                <section className="dashboard-card dashboard-card--red rounded-2xl border p-5">
                    <h2 className="font-semibold">
                        Permission kedisiplinan_santri.view diperlukan
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
