import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { PerizinanSantriAccessDenied } from '../components/PerizinanSantriAccessDenied';
import { PerizinanSantriDetailPanel } from '../components/PerizinanSantriDetailPanel';
import type { StudentPermitShowPageProps } from '../types';

export default function Show() {
    const {
        auth,
        permit,
        canManage,
        canApprove,
        canCheckout,
        canReturn,
        canArchive,
    } = usePage<StudentPermitShowPageProps>().props;

    if (!canAccess(auth, 'perizinan_santri.view')) {
        return <PerizinanSantriAccessDenied />;
    }

    return (
        <>
            <Head title={`${permit.permit_no} - Perizinan Santri`} />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title={permit.permit_no}
                description="Detail izin santri, snapshot wali, status lifecycle, catatan keputusan, return, void, dan histori revisi."
            >
                <PerizinanSantriDetailPanel
                    permit={permit}
                    canManage={canManage}
                    canApprove={canApprove}
                    canCheckout={canCheckout}
                    canReturn={canReturn}
                    canArchive={canArchive}
                />
            </SystemDashboardLayout>
        </>
    );
}
