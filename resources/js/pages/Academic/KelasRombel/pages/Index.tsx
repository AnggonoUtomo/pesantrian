import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { KelasRombelDashboard } from '../components/KelasRombelDashboard';

export default function Index() {
    return (
        <>
            <Head title="Kelas / Rombel / Kurikulum" />
            <SystemDashboardLayout
                eyebrow="Academic"
                title="Kelas / Rombel / Kurikulum"
                description="Tinjau kelas, rombongan belajar, kurikulum, penempatan santri, dan wali kelas pada periode akademik."
            >
                <KelasRombelDashboard />
            </SystemDashboardLayout>
        </>
    );
}
