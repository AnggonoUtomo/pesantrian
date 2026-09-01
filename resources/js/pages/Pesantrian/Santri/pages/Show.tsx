import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { SantriAccessDenied } from '../components/SantriAccessDenied';
import { SantriDetailPanel } from '../components/SantriDetailPanel';
import type { StudentShowPageProps } from '../types';

export default function Show() {
    const { auth, student, primaryUnitOptions } =
        usePage<StudentShowPageProps>().props;

    if (!canAccess(auth, 'santri.view')) {
        return <SantriAccessDenied />;
    }

    return (
        <>
            <Head title={`${student.student_no} - Data Induk Santri`} />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title={student.full_name}
                description="Detail data induk, wali snapshot, asal PPDB, dan status lifecycle santri."
            >
                <SantriDetailPanel
                    student={student}
                    primaryUnitOptions={primaryUnitOptions}
                />
            </SystemDashboardLayout>
        </>
    );
}
