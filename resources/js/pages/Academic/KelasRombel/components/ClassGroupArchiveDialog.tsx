import { useForm } from '@inertiajs/react';
import { Archive, RotateCcw } from 'lucide-react';
import { useEffect } from 'react';
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
import type { ClassGroup } from '../types';
import { KelasRombelTextareaField } from './KelasRombelFormFields';

type ArchivePayload = {
    reason: string;
};

type Props = {
    open: boolean;
    classGroup: ClassGroup;
    mode: 'archive' | 'restore';
    onOpenChange: (open: boolean) => void;
};

export function ClassGroupArchiveDialog({
    open,
    classGroup,
    mode,
    onOpenChange,
}: Props) {
    const isRestore = mode === 'restore';
    const form = useForm<ArchivePayload>({ reason: '' });

    useEffect(() => {
        if (open) {
            form.reset();
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, mode, classGroup.id]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const url = isRestore
            ? routeOr(
                  `/academic/class-groups/${classGroup.id}/restore`,
                  'academic.class-groups.restore',
                  classGroup.id,
              )
            : routeOr(
                  `/academic/class-groups/${classGroup.id}/archive`,
                  'academic.class-groups.archive',
                  classGroup.id,
              );

        form.patch(String(url), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => !form.processing && onOpenChange(nextOpen)}
        >
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--amber flex size-10 items-center justify-center rounded-lg">
                            {isRestore ? (
                                <RotateCcw className="size-5" aria-hidden="true" />
                            ) : (
                                <Archive className="size-5" aria-hidden="true" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isRestore ? 'Pulihkan rombel' : 'Arsipkan rombel'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                {isRestore
                                    ? 'Rombel akan aktif kembali sesuai status terakhir yang dipulihkan.'
                                    : 'Rombel diarsipkan agar tidak dipakai untuk operasional baru.'}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    {!isRestore ? (
                        <KelasRombelTextareaField
                            id="archive-reason"
                            label="Alasan arsip"
                            value={form.data.reason}
                            error={form.errors.reason}
                            placeholder="Contoh: Rombel sudah tidak dipakai pada semester ini"
                            onChange={(value) => form.setData('reason', value)}
                        />
                    ) : (
                        <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                            Pastikan rombel {classGroup.code} memang akan
                            digunakan kembali sebelum dipulihkan.
                        </p>
                    )}
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
                            loading={form.processing}
                            variant={isRestore ? 'default' : 'destructive'}
                        >
                            {form.processing
                                ? 'Memproses...'
                                : isRestore
                                  ? 'Pulihkan rombel'
                                  : 'Arsipkan rombel'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
