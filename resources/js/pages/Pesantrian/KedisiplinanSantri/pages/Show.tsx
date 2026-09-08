import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { KedisiplinanSantriAccessDenied } from '../components/KedisiplinanSantriAccessDenied';
import { KedisiplinanSantriDetailPanel } from '../components/KedisiplinanSantriDetailPanel';
import type { StudentDisciplineShowPageProps } from '../types';

export default function Show() {
    const {
        auth,
        case: disciplineCase,
        options,
        canManage,
        canReview,
        canResolve,
        canArchive,
    } =
        usePage<StudentDisciplineShowPageProps>().props;

    if (!canAccess(auth, 'kedisiplinan_santri.view')) {
        return <KedisiplinanSantriAccessDenied />;
    }

    return (
        <>
            <Head
                title={`${disciplineCase.case_no} - Pelanggaran / Kedisiplinan`}
            />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title={disciplineCase.case_no}
                description="Detail kasus kedisiplinan santri, kategori, tindakan pembinaan, status final, dan histori revisi."
            >
                <KedisiplinanSantriDetailPanel
                    case={disciplineCase}
                    options={options}
                    canManage={canManage}
                    canReview={canReview}
                    canResolve={canResolve}
                    canArchive={canArchive}
                />
            </SystemDashboardLayout>
        </>
    );
}
