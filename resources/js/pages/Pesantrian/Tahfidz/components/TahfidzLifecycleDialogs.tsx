import { useForm } from '@inertiajs/react';
import { Ban, CheckCircle2, RotateCcw } from 'lucide-react';
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
import { SelectItem } from '@/components/ui/select';
import { routeOr } from '@/lib/route';
import type {
    TahfidzReasonPayload,
    TahfidzReviewPayload,
    TahfidzSubmission,
} from '../types';
import {
    TahfidzCancelButton,
    TahfidzFieldError,
    TahfidzSelectField,
    TahfidzTextareaField,
} from './TahfidzFormFields';

type DialogProps = {
    open: boolean;
    submission: TahfidzSubmission;
    onOpenChange: (open: boolean) => void;
};

export function ReviewTahfidzDialog({
    open,
    submission,
    onOpenChange,
}: DialogProps) {
    const form = useForm<TahfidzReviewPayload>({
        status: 'accepted',
        reason: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/tahfidz/submissions/${submission.id}/review`,
                'pesantrian.tahfidz.submissions.review',
                submission.id,
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
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            {form.data.status === 'accepted' ? (
                                <CheckCircle2 className="size-5" aria-hidden="true" />
                            ) : (
                                <RotateCcw className="size-5" aria-hidden="true" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>Review setoran tahfidz</DialogTitle>
                            <DialogDescription className="mt-1">
                                Review hanya untuk setoran berstatus menunggu
                                review.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    <TahfidzSelectField
                        id="tahfidz-review-status"
                        label="Keputusan review"
                        value={form.data.status}
                        error={form.errors.status}
                        onChange={(value) =>
                            form.setData(
                                'status',
                                value as TahfidzReviewPayload['status'],
                            )
                        }
                    >
                        <SelectItem value="accepted">Diterima</SelectItem>
                        <SelectItem value="needs_revision">
                            Perlu koreksi
                        </SelectItem>
                    </TahfidzSelectField>
                    <TahfidzTextareaField
                        id="tahfidz-review-reason"
                        label="Alasan review"
                        value={form.data.reason}
                        error={form.errors.reason}
                        placeholder="Contoh: Hafalan lancar dan siap diterima"
                        required
                        onChange={(value) => form.setData('reason', value)}
                    />
                    <DialogFooter>
                        <TahfidzCancelButton
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        />
                        <LoadingButton type="submit" loading={form.processing}>
                            Simpan review
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export function VoidTahfidzDialog({
    open,
    submission,
    onOpenChange,
}: DialogProps) {
    const form = useForm<TahfidzReasonPayload>({ reason: '' });
    const errors = form.errors as Partial<
        Record<keyof TahfidzReasonPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/tahfidz/submissions/${submission.id}/void`,
                'pesantrian.tahfidz.submissions.void',
                submission.id,
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
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--amber flex size-10 items-center justify-center rounded-lg">
                            <Ban className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Batalkan setoran tahfidz</DialogTitle>
                            <DialogDescription className="mt-1">
                                Data tidak dihapus, hanya diberi status void
                                dengan alasan audit.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <TahfidzFieldError message={errors.status} />
                    ) : null}
                    <TahfidzTextareaField
                        id="tahfidz-void-reason"
                        label="Alasan pembatalan"
                        value={form.data.reason}
                        error={errors.reason}
                        placeholder="Contoh: salah input tanggal dan dibuat ulang"
                        required
                        onChange={(value) => form.setData('reason', value)}
                    />
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton
                            type="submit"
                            variant="destructive"
                            loading={form.processing}
                        >
                            Batalkan setoran
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
