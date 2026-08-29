import { useForm } from '@inertiajs/react';
import { CalendarCheck2, LockKeyhole } from 'lucide-react';
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
import route from '@/lib/route';
import type { AcademicTerm } from '../types';

type AcademicTermLifecycleDialogProps = {
    action: 'activate' | 'close';
    open: boolean;
    term: AcademicTerm | null;
    onOpenChange: (open: boolean) => void;
};

export function AcademicTermLifecycleDialog({
    action,
    open,
    term,
    onOpenChange,
}: AcademicTermLifecycleDialogProps) {
    const form = useForm<{ term?: string }>({});
    const isActivate = action === 'activate';
    const Icon = isActivate ? CalendarCheck2 : LockKeyhole;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!term) {
            return;
        }

        form.patch(
            route(
                isActivate
                    ? 'academic.periods.terms.activate'
                    : 'academic.periods.terms.close',
                term.id,
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
                    <div className="flex items-start gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <Icon className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>
                                {isActivate
                                    ? 'Aktifkan term akademik'
                                    : 'Tutup term akademik'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                {isActivate
                                    ? 'Term ini akan menjadi periode aktif global.'
                                    : 'Term ditutup dan tidak lagi menjadi periode aktif.'}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="rounded-xl border bg-muted/30 p-4">
                        <p className="text-sm font-medium">
                            {term?.name ?? 'Term akademik'}
                        </p>
                        <p className="mt-1 text-xs text-foreground/60">
                            {term?.code ?? '-'} · {term?.starts_on ?? '-'} s/d{' '}
                            {term?.ends_on ?? '-'}
                        </p>
                    </div>
                    {form.errors.term ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            {form.errors.term}
                        </p>
                    ) : null}
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                            disabled={form.processing}
                        >
                            Batal
                        </Button>
                        <LoadingButton
                            type="submit"
                            variant={isActivate ? 'default' : 'destructive'}
                            loading={form.processing}
                        >
                            {form.processing
                                ? 'Memproses...'
                                : isActivate
                                  ? 'Aktifkan term'
                                  : 'Tutup term'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
