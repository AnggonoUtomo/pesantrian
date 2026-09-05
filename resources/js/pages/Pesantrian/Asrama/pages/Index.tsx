import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { AsramaDashboard } from '../components/AsramaDashboard';

export default function Index() {
    return (
        <>
            <Head title="Asrama" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Asrama"
                description="Tinjau asrama, kamar, kapasitas, hunian santri, dan musyrif secara bertahap."
            >
                <AsramaDashboard />
            </SystemDashboardLayout>
        </>
    );
}
