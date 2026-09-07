import { useForm } from '@inertiajs/react';
import {
    Ban,
    CheckCircle2,
    DoorOpen,
    Send,
    XCircle,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { FormEvent } from 'react';
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
import type { RouteName } from '@/lib/route';
import type {
    StudentPermit,
    StudentPermitReasonPayload,
    StudentPermitReturnPayload,
    StudentPermitReviewPayload,
} from '../types';
import { permitStatusLabel } from './perizinanSantriDisplay';
import {
    fromDateTimeLocal,
    PerizinanFieldError,
    PerizinanTextareaField,
    PerizinanTextField,
    toDateTimeLocal,
} from './PerizinanSantriFormFields';

type DialogProps = {
    open: boolean;
    permit: StudentPermit;
    onOpenChange: (open: boolean) => void;
};

export function SubmitPerizinanDialog({
    open,
    permit,
    onOpenChange,
}: DialogProps) {
    return (
        <SimpleLifecycleDialog
            open={open}
            permit={permit}
            title="Submit permohonan izin"
            description="Permohonan draft akan diajukan untuk review petugas."
            confirmLabel="Submit izin"
            routeName="pesantrian.student-permits.submit"
            fallback={`/pesantrian/student-permits/${permit.id}/submit`}
            icon={Send}
            onOpenChange={onOpenChange}
        />
    );
}

export function CheckoutPerizinanDialog({
    open,
    permit,
    onOpenChange,
}: DialogProps) {
    return (
        <SimpleLifecycleDialog
            open={open}
            permit={permit}
            title="Check-out santri"
            description="Catat santri sudah keluar sesuai izin yang disetujui."
            confirmLabel="Catat check-out"
            routeName="pesantrian.student-permits.checkout"
            fallback={`/pesantrian/student-permits/${permit.id}/checkout`}
            icon={DoorOpen}
            onOpenChange={onOpenChange}
        />
    );
}

export function ApprovePerizinanDialog({
    open,
    permit,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentPermitReviewPayload>({ review_note: null });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/student-permits/${permit.id}/approve`,
                'pesantrian.student-permits.approve',
                permit.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            },
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Setujui izin santri</DialogTitle>
                    <DialogDescription>
                        Approve hanya untuk izin berstatus menunggu review.
                    </DialogDescription>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    <PerizinanTextareaField
                        id="student-permit-approve-note"
                        label="Catatan review"
                        value={form.data.review_note ?? ''}
                        error={form.errors.review_note}
                        placeholder="Opsional: catatan untuk operator"
                        onChange={(value) =>
                            form.setData('review_note', value || null)
                        }
                    />
                    <LifecycleFooter
                        processing={form.processing}
                        submitLabel="Setujui izin"
                        onCancel={() => onOpenChange(false)}
                    />
                </form>
            </DialogContent>
        </Dialog>
    );
}

export function RejectPerizinanDialog({
    open,
    permit,
    onOpenChange,
}: DialogProps) {
    return (
        <ReasonLifecycleDialog
            open={open}
            permit={permit}
            title="Tolak izin santri"
            description="Tolak permohonan dengan alasan yang jelas untuk histori."
            label="Alasan penolakan"
            placeholder="Contoh: wali belum terkonfirmasi"
            confirmLabel="Tolak izin"
            routeName="pesantrian.student-permits.reject"
            fallback={`/pesantrian/student-permits/${permit.id}/reject`}
            destructive
            onOpenChange={onOpenChange}
        />
    );
}

export function ReturnPerizinanDialog({
    open,
    permit,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentPermitReturnPayload>({
        returned_at: toDateTimeLocal(new Date().toISOString()),
        return_note: null,
    });
    const errors = form.errors as Partial<
        Record<keyof StudentPermitReturnPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            returned_at: fromDateTimeLocal(data.returned_at),
            return_note: data.return_note || null,
        }));
        form.patch(
            routeOr(
                `/pesantrian/student-permits/${permit.id}/return`,
                'pesantrian.student-permits.return',
                permit.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            },
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Catat santri kembali</DialogTitle>
                    <DialogDescription>
                        Return/check-in hanya untuk izin yang sedang berjalan.
                    </DialogDescription>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <PerizinanFieldError message={errors.status} />
                    ) : null}
                    <PerizinanTextField
                        id="student-permit-returned-at"
                        label="Waktu kembali"
                        type="datetime-local"
                        value={form.data.returned_at}
                        error={errors.returned_at}
                        required
                        onChange={(value) =>
                            form.setData('returned_at', value)
                        }
                    />
                    <PerizinanTextareaField
                        id="student-permit-return-note"
                        label="Catatan kembali"
                        value={form.data.return_note ?? ''}
                        error={errors.return_note}
                        placeholder="Opsional: catatan keterlambatan atau kondisi santri"
                        onChange={(value) =>
                            form.setData('return_note', value || null)
                        }
                    />
                    <LifecycleFooter
                        processing={form.processing}
                        submitLabel="Catat kembali"
                        onCancel={() => onOpenChange(false)}
                    />
                </form>
            </DialogContent>
        </Dialog>
    );
}

export function VoidPerizinanDialog({
    open,
    permit,
    onOpenChange,
}: DialogProps) {
    return (
        <ReasonLifecycleDialog
            open={open}
            permit={permit}
            title="Batalkan izin santri"
            description="Data tidak dihapus, hanya diberi status void dengan alasan audit."
            label="Alasan pembatalan"
            placeholder="Contoh: salah input tanggal dan dibuat ulang"
            confirmLabel="Batalkan izin"
            routeName="pesantrian.student-permits.void"
            fallback={`/pesantrian/student-permits/${permit.id}/void`}
            destructive
            onOpenChange={onOpenChange}
        />
    );
}

function SimpleLifecycleDialog({
    open,
    permit,
    title,
    description,
    confirmLabel,
    routeName,
    fallback,
    icon: Icon,
    onOpenChange,
}: DialogProps & {
    title: string;
    description: string;
    confirmLabel: string;
    routeName: Exclude<RouteName, undefined>;
    fallback: string;
    icon: LucideIcon;
}) {
    const form = useForm({});

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(routeOr(fallback, routeName, permit.id), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            <Icon className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>{title}</DialogTitle>
                            <DialogDescription className="mt-1">
                                {description}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {'status' in form.errors ? (
                        <PerizinanFieldError
                            message={String(form.errors.status)}
                        />
                    ) : null}
                    <p className="rounded-xl border bg-muted/40 p-3 text-sm text-foreground/70">
                        {permit.permit_no} · {permit.student_name} · status
                        sekarang {permitStatusLabel(permit.status)}
                    </p>
                    <LifecycleFooter
                        processing={form.processing}
                        submitLabel={confirmLabel}
                        onCancel={() => onOpenChange(false)}
                    />
                </form>
            </DialogContent>
        </Dialog>
    );
}

function ReasonLifecycleDialog({
    open,
    permit,
    title,
    description,
    label,
    placeholder,
    confirmLabel,
    routeName,
    fallback,
    destructive = false,
    onOpenChange,
}: DialogProps & {
    title: string;
    description: string;
    label: string;
    placeholder: string;
    confirmLabel: string;
    routeName: Exclude<RouteName, undefined>;
    fallback: string;
    destructive?: boolean;
}) {
    const form = useForm<StudentPermitReasonPayload>({ reason: '' });
    const errors = form.errors as Partial<
        Record<keyof StudentPermitReasonPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(routeOr(fallback, routeName, permit.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--amber flex size-10 items-center justify-center rounded-lg">
                            {destructive ? (
                                <Ban className="size-5" aria-hidden="true" />
                            ) : (
                                <XCircle
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </span>
                        <div>
                            <DialogTitle>{title}</DialogTitle>
                            <DialogDescription className="mt-1">
                                {description}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <PerizinanFieldError message={errors.status} />
                    ) : null}
                    <PerizinanTextareaField
                        id={`student-permit-${permit.id}-reason`}
                        label={label}
                        value={form.data.reason}
                        error={errors.reason}
                        placeholder={placeholder}
                        required
                        onChange={(value) => form.setData('reason', value)}
                    />
                    <LifecycleFooter
                        processing={form.processing}
                        submitLabel={confirmLabel}
                        destructive={destructive}
                        onCancel={() => onOpenChange(false)}
                    />
                </form>
            </DialogContent>
        </Dialog>
    );
}

function LifecycleFooter({
    processing,
    submitLabel,
    destructive = false,
    onCancel,
}: {
    processing: boolean;
    submitLabel: string;
    destructive?: boolean;
    onCancel: () => void;
}) {
    return (
        <DialogFooter>
            <Button
                type="button"
                variant="outline"
                disabled={processing}
                onClick={onCancel}
            >
                Batal
            </Button>
            <LoadingButton
                type="submit"
                variant={destructive ? 'destructive' : 'default'}
                loading={processing}
            >
                {destructive ? (
                    <Ban className="size-4" aria-hidden="true" />
                ) : (
                    <CheckCircle2 className="size-4" aria-hidden="true" />
                )}
                {submitLabel}
            </LoadingButton>
        </DialogFooter>
    );
}
