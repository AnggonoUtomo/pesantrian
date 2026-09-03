import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';

export function KelasRombelAccessDenied() {
    return (
        <>
            <Head title="Kelas / Rombel / Kurikulum" />
            <SystemDashboardLayout
                eyebrow="Academic"
                title="Kelas / Rombel / Kurikulum"
                description="Kelola kelas, rombongan belajar, kurikulum, penempatan santri, dan wali kelas."
            >
                <section className="dashboard-card dashboard-card--rose rounded-2xl border p-6 text-sm text-foreground/75">
                    Anda belum memiliki izin untuk melihat data Kelas / Rombel /
                    Kurikulum.
                </section>
            </SystemDashboardLayout>
        </>
    );
}
