import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { KedisiplinanSantriDashboard } from '../components/KedisiplinanSantriDashboard';

export default function Index() {
    return (
        <>
            <Head title="Pelanggaran / Kedisiplinan" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Pelanggaran / Kedisiplinan"
                description="Pantau catatan pelanggaran santri, status review, tindakan pembinaan, penyelesaian, dan histori revisi."
            >
                <KedisiplinanSantriDashboard />
            </SystemDashboardLayout>
        </>
    );
}
