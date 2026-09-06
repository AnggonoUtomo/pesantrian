import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { PresensiSantriDashboard } from '../components/PresensiSantriDashboard';

export default function Index() {
    return (
        <>
            <Head title="Presensi Santri" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Presensi Santri"
                description="Tinjau sesi presensi kelas, asrama, dan kegiatan umum beserta ringkasan kehadiran santri."
            >
                <PresensiSantriDashboard />
            </SystemDashboardLayout>
        </>
    );
}
