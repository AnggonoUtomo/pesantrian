import { router } from '@inertiajs/react';
import { CheckCircle2, ShieldCheck, XCircle } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { LoadingButton } from '@/components/ui/loading-button';
import { routeOr } from '@/lib/route';
import type { AdmissionStatus, StudentAdmission } from '../types';

type LifecycleAction = {
    status: AdmissionStatus;
    routeName:
        | 'api.v1.pesantrian.admissions.verify'
        | 'api.v1.pesantrian.admissions.accept'
        | 'api.v1.pesantrian.admissions.reject'
        | 'api.v1.pesantrian.admissions.cancel';
    fallbackPath: (admission: StudentAdmission) => string;
    label: string;
    title: string;
    description: string;
    confirmLabel: string;
    successMessage: string;
    variant: 'default' | 'secondary' | 'destructive';
};

type Props = {
    admission: StudentAdmission;
};

const actionsByCurrentStatus: Partial<Record<AdmissionStatus, LifecycleAction[]>> = {
    submitted: [
        {
            status: 'verified',
            routeName: 'api.v1.pesantrian.admissions.verify',
            fallbackPath: (admission) =>
                `/api/v1/pesantrian/admissions/${admission.id}/verify`,
            label: 'Verifikasi pendaftaran',
            title: 'Verifikasi pendaftaran?',
            description:
                'Status akan berubah menjadi terverifikasi dan siap diputuskan diterima atau ditolak.',
            confirmLabel: 'Ya, verifikasi',
            successMessage: 'Pendaftaran santri berhasil diverifikasi.',
            variant: 'default',
        },
        cancelAction(),
    ],
    verified: [
        {
            status: 'accepted',
            routeName: 'api.v1.pesantrian.admissions.accept',
            fallbackPath: (admission) =>
                `/api/v1/pesantrian/admissions/${admission.id}/accept`,
            label: 'Terima santri',
            title: 'Terima calon santri?',
            description:
                'Status akan berubah menjadi diterima. Data santri aktif belum dibuat sampai module Data Induk Santri tersedia.',
            confirmLabel: 'Ya, terima',
            successMessage: 'Pendaftaran santri berhasil diterima.',
            variant: 'default',
        },
        {
            status: 'rejected',
            routeName: 'api.v1.pesantrian.admissions.reject',
            fallbackPath: (admission) =>
                `/api/v1/pesantrian/admissions/${admission.id}/reject`,
            label: 'Tolak pendaftaran',
            title: 'Tolak pendaftaran?',
            description:
                'Status akan berubah menjadi ditolak dan tidak bisa diproses lagi pada baseline awal.',
            confirmLabel: 'Ya, tolak',
            successMessage: 'Pendaftaran santri berhasil ditolak.',
            variant: 'destructive',
        },
        cancelAction(),
    ],
    draft: [cancelAction()],
};

export function AdmissionLifecycleActions({ admission }: Props) {
    const actions = actionsByCurrentStatus[admission.status] ?? [];
    const [selectedAction, setSelectedAction] =
        useState<LifecycleAction | null>(null);
    const [processing, setProcessing] = useState(false);

    if (actions.length === 0) {
        return (
            <p className="text-xs text-foreground/55">
                Tidak ada aksi lifecycle.
            </p>
        );
    }

    const confirm = async () => {
        if (selectedAction === null) {
            return;
        }

        setProcessing(true);

        const response = await submitLifecycle(admission, selectedAction);

        setProcessing(false);

        if (!response.ok) {
            toast.error(response.message);

            return;
        }

        toast.success(selectedAction.successMessage);
        setSelectedAction(null);
        router.reload({ only: ['admissions'] });
    };

    return (
        <div className="flex flex-wrap gap-2">
            {actions.map((action) => (
                <Button
                    key={action.status}
                    type="button"
                    variant={action.variant}
                    size="sm"
                    onClick={() => setSelectedAction(action)}
                >
                    <ActionIcon status={action.status} />
                    {action.label}
                </Button>
            ))}

            <Dialog
                open={selectedAction !== null}
                onOpenChange={(open) => {
                    if (!open && !processing) {
                        setSelectedAction(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{selectedAction?.title}</DialogTitle>
                        <DialogDescription>
                            {selectedAction?.description}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="rounded-lg border bg-muted/40 p-3 text-sm">
                        <div className="font-medium">
                            {admission.candidate_name}
                        </div>
                        <div className="text-foreground/60">
                            {admission.registration_no}
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setSelectedAction(null)}
                            disabled={processing}
                        >
                            Kembali
                        </Button>
                        <LoadingButton
                            type="button"
                            variant={selectedAction?.variant ?? 'default'}
                            loading={processing}
                            onClick={confirm}
                        >
                            {selectedAction?.confirmLabel ?? 'Konfirmasi'}
                        </LoadingButton>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

function cancelAction(): LifecycleAction {
    return {
        status: 'cancelled',
        routeName: 'api.v1.pesantrian.admissions.cancel',
        fallbackPath: (admission) =>
            `/api/v1/pesantrian/admissions/${admission.id}/cancel`,
        label: 'Batalkan pendaftaran',
        title: 'Batalkan pendaftaran?',
        description:
            'Status akan berubah menjadi dibatalkan dan tidak bisa diproses lagi pada baseline awal.',
        confirmLabel: 'Ya, batalkan',
        successMessage: 'Pendaftaran santri berhasil dibatalkan.',
        variant: 'secondary',
    };
}

function ActionIcon({ status }: { status: AdmissionStatus }) {
    if (status === 'verified') {
        return <ShieldCheck className="size-4" />;
    }

    if (status === 'accepted') {
        return <CheckCircle2 className="size-4" />;
    }

    return <XCircle className="size-4" />;
}

async function submitLifecycle(
    admission: StudentAdmission,
    action: LifecycleAction,
): Promise<{ ok: true } | { ok: false; message: string }> {
    const response = await fetch(
        routeOr(action.fallbackPath(admission), action.routeName, admission.id),
        {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'Idempotency-Key': idempotencyKey(),
                'X-Requested-With': 'XMLHttpRequest',
                ...csrfHeaders(),
            },
            body: JSON.stringify({}),
        },
    );
    const data = (await response.json().catch(() => null)) as
        | { message?: string }
        | null;

    if (response.ok) {
        return { ok: true };
    }

    return {
        ok: false,
        message: data?.message ?? 'Status pendaftaran tidak dapat diproses.',
    };
}

function csrfHeaders(): Record<string, string> {
    const token = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
        ?.split('=')
        .slice(1)
        .join('=');

    return token ? { 'X-XSRF-TOKEN': decodeURIComponent(token) } : {};
}

function idempotencyKey(): string {
    if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
        return crypto.randomUUID();
    }

    return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}
