import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { TahfidzAccessDenied } from '../components/TahfidzAccessDenied';
import { TahfidzDetailPanel } from '../components/TahfidzDetailPanel';
import type { TahfidzShowPageProps } from '../types';

export default function Show() {
    const {
        auth,
        submission,
        options,
        canManage,
        canRecord,
        canReview,
        canArchive,
    } = usePage<TahfidzShowPageProps>().props;
    const canView = canAccess(auth, 'tahfidz.view');

    return (
        <>
            <Head title={`${submission.student_no} - Tahfidz / Hafalan`} />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Tahfidz / Hafalan"
                description="Detail setoran, target hafalan, pembimbing, status review, dan riwayat koreksi."
            >
                {canView ? (
                    <TahfidzDetailPanel
                        submission={submission}
                        options={options}
                        canManage={canManage}
                        canRecord={canRecord}
                        canReview={canReview}
                        canArchive={canArchive}
                    />
                ) : (
                    <TahfidzAccessDenied />
                )}
            </SystemDashboardLayout>
        </>
    );
}
