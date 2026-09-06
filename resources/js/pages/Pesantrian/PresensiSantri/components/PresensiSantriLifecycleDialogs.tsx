import { useForm } from '@inertiajs/react';
import { Ban, CheckCircle2, RefreshCcw } from 'lucide-react';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoadingButton } from '@/components/ui/loading-button';
import { routeOr } from '@/lib/route';
import type {
    StudentAttendance,
    StudentAttendanceReasonPayload,
} from '../types';

type ConfirmationProps = {
    open: boolean;
    attendance: StudentAttendance;
    onOpenChange: (open: boolean) => void;
};

type ReasonProps = ConfirmationProps & {
    mode: 'revise' | 'void';
};

export function SubmitPresensiSantriDialog({
    open,
    attendance,
    onOpenChange,
}: ConfirmationProps) {
    const form = useForm({});

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/student-attendances/${attendance.id}/submit`,
                'pesantrian.student-attendances.submit',
                attendance.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
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
                        <span className="dashboard-icon dashboard-accent--teal flex size-10 items-center justify-center rounded-lg">
                            <CheckCircle2
                                className="size-5"
                                aria-hidden="true"
                            />
                        </span>
                        <div>
                            <DialogTitle>Submit presensi</DialogTitle>
                            <DialogDescription className="mt-1">
                                Setelah disubmit, sesi presensi terkunci dan
                                koreksi berikutnya harus lewat jalur revisi.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    <p className="text-sm text-foreground/70">
                        Submit sesi {attendance.session_code}?
                    </p>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={form.processing}>
                            Submit presensi
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export function ReasonPresensiSantriDialog({
    open,
    attendance,
    mode,
    onOpenChange,
}: ReasonProps) {
    const form = useForm<StudentAttendanceReasonPayload>({ reason: '' });
    const isVoid = mode === 'void';
    const title = isVoid ? 'Batalkan sesi presensi' : 'Buka revisi';
    const url = isVoid
        ? routeOr(
              `/pesantrian/student-attendances/${attendance.id}/void`,
              'pesantrian.student-attendances.void',
              attendance.id,
          )
        : routeOr(
              `/pesantrian/student-attendances/${attendance.id}/revise`,
              'pesantrian.student-attendances.revise',
              attendance.id,
          );

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(url, {
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
                            {isVoid ? (
                                <Ban className="size-5" aria-hidden="true" />
                            ) : (
                                <RefreshCcw
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </span>
                        <div>
                            <DialogTitle>{title}</DialogTitle>
                            <DialogDescription className="mt-1">
                                Alasan wajib diisi agar perubahan lifecycle
                                mudah diaudit.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    <div className="space-y-2">
                        <Label htmlFor={`attendance-${mode}-reason`}>
                            Alasan
                        </Label>
                        <Input
                            id={`attendance-${mode}-reason`}
                            value={form.data.reason}
                            placeholder={
                                isVoid
                                    ? 'Contoh: sesi salah tanggal'
                                    : 'Contoh: koreksi dari wali kelas'
                            }
                            aria-invalid={form.errors.reason ? true : undefined}
                            onChange={(event) =>
                                form.setData('reason', event.target.value)
                            }
                        />
                        {form.errors.reason ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.reason}
                            </p>
                        ) : null}
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={form.processing}>
                            {title}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
