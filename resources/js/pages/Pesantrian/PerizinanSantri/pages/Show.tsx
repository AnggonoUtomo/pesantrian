import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { PerizinanSantriAccessDenied } from '../components/PerizinanSantriAccessDenied';
import { PerizinanSantriDetailPanel } from '../components/PerizinanSantriDetailPanel';
import {
    ApprovePerizinanDialog,
    CheckoutPerizinanDialog,
    RejectPerizinanDialog,
    ReturnPerizinanDialog,
    SubmitPerizinanDialog,
    VoidPerizinanDialog,
} from '../components/PerizinanSantriLifecycleDialogs';
import { PerizinanSantriMutationDialog } from '../components/PerizinanSantriMutationDialog';
import type { StudentPermitShowPageProps } from '../types';

export default function Show() {
    const {
        auth,
        permit,
        options,
        canManage,
        canApprove,
        canCheckout,
        canReturn,
        canArchive,
    } = usePage<StudentPermitShowPageProps>().props;
    const [mutationDialogOpen, setMutationDialogOpen] = useState(false);
    const [submitDialogOpen, setSubmitDialogOpen] = useState(false);
    const [approveDialogOpen, setApproveDialogOpen] = useState(false);
    const [rejectDialogOpen, setRejectDialogOpen] = useState(false);
    const [checkoutDialogOpen, setCheckoutDialogOpen] = useState(false);
    const [returnDialogOpen, setReturnDialogOpen] = useState(false);
    const [voidDialogOpen, setVoidDialogOpen] = useState(false);

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
                    onEdit={() => setMutationDialogOpen(true)}
                    onSubmitPermit={() => setSubmitDialogOpen(true)}
                    onApprove={() => setApproveDialogOpen(true)}
                    onReject={() => setRejectDialogOpen(true)}
                    onCheckout={() => setCheckoutDialogOpen(true)}
                    onReturn={() => setReturnDialogOpen(true)}
                    onVoid={() => setVoidDialogOpen(true)}
                />
            </SystemDashboardLayout>

            <PerizinanSantriMutationDialog
                open={mutationDialogOpen}
                permit={permit}
                options={options}
                onOpenChange={setMutationDialogOpen}
            />
            <SubmitPerizinanDialog
                open={submitDialogOpen}
                permit={permit}
                onOpenChange={setSubmitDialogOpen}
            />
            <ApprovePerizinanDialog
                open={approveDialogOpen}
                permit={permit}
                onOpenChange={setApproveDialogOpen}
            />
            <RejectPerizinanDialog
                open={rejectDialogOpen}
                permit={permit}
                onOpenChange={setRejectDialogOpen}
            />
            <CheckoutPerizinanDialog
                open={checkoutDialogOpen}
                permit={permit}
                onOpenChange={setCheckoutDialogOpen}
            />
            <ReturnPerizinanDialog
                open={returnDialogOpen}
                permit={permit}
                onOpenChange={setReturnDialogOpen}
            />
            <VoidPerizinanDialog
                open={voidDialogOpen}
                permit={permit}
                onOpenChange={setVoidDialogOpen}
            />
        </>
    );
}
