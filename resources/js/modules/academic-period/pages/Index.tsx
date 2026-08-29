import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { AcademicPeriodDashboard } from '../components/AcademicPeriodDashboard';

export default function Index() {
    return (
        <>
            <Head title="Periode Akademik" />
            <SystemDashboardLayout
                eyebrow="Academic"
                title="Periode Akademik"
                description="Kelola visibilitas tahun akademik, semester, dan term aktif sebagai kalender dasar operasional pesantren."
            >
                <AcademicPeriodDashboard />
            </SystemDashboardLayout>
        </>
    );
}
