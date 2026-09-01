import { useForm } from '@inertiajs/react';
import { Archive } from 'lucide-react';
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
import type { Student, StudentArchivePayload } from '../types';

type Props = {
    open: boolean;
    student: Student | null;
    onOpenChange: (open: boolean) => void;
};

export function SantriArchiveDialog({ open, student, onOpenChange }: Props) {
    const form = useForm<StudentArchivePayload>({ reason: null });
    const errors = form.errors as Partial<Record<keyof StudentArchivePayload, string>>;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (student === null) {
            return;
        }

        form.patch(
            routeOr(
                `/pesantrian/students/${student.id}/archive`,
                'pesantrian.students.archive',
                student.id,
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
                        <span className="dashboard-icon dashboard-accent--yellow flex size-10 items-center justify-center rounded-lg">
                            <Archive className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Arsipkan santri</DialogTitle>
                            <DialogDescription className="mt-1">
                                Data tidak tampil di daftar aktif, tetapi masih
                                bisa dipulihkan dari filter arsip.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form className="space-y-4" onSubmit={submit}>
                    <p className="rounded-lg border bg-muted/40 p-3 text-sm">
                        Arsipkan <strong>{student?.full_name}</strong>?
                        Tindakan ini aman dan dapat dipulihkan.
                    </p>
                    <div className="space-y-2">
                        <Label htmlFor="student-archive-reason">
                            Alasan arsip
                        </Label>
                        <Input
                            id="student-archive-reason"
                            value={form.data.reason ?? ''}
                            placeholder="Opsional"
                            aria-invalid={errors.reason ? true : undefined}
                            onChange={(event) =>
                                form.setData('reason', nullable(event.target.value))
                            }
                        />
                        {errors.reason ? (
                            <FieldError message={errors.reason} />
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
                        <LoadingButton
                            type="submit"
                            variant="destructive"
                            loading={form.processing}
                        >
                            Arsipkan
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function FieldError({ message }: { message: string }) {
    return (
        <p className="text-xs text-destructive" role="alert">
            {message}
        </p>
    );
}

function nullable(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}
