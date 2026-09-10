import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { PrestasiSantriDashboard } from '../components/PrestasiSantriDashboard';

export default function Index() {
    return (
        <>
            <Head title="Prestasi Santri" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Prestasi Santri"
                description="Pantau catatan prestasi santri, status verifikasi, kategori, tingkat capaian, dan histori revisi."
            >
                <PrestasiSantriDashboard />
            </SystemDashboardLayout>
        </>
    );
}
