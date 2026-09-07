import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { TahfidzDashboard } from '../components/TahfidzDashboard';

export default function Index() {
    return (
        <>
            <Head title="Tahfidz / Hafalan" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Tahfidz / Hafalan"
                description="Tinjau program, target, setoran, murojaah, review, dan progres hafalan santri."
            >
                <TahfidzDashboard />
            </SystemDashboardLayout>
        </>
    );
}
