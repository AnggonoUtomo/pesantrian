import { useForm } from '@inertiajs/react';
import { RotateCcw } from 'lucide-react';
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
import type { OrganizationUnit } from '../types';

type Props = {
    open: boolean;
    unit: OrganizationUnit | null;
    onOpenChange: (open: boolean) => void;
};

export function RestoreOrganizationUnitDialog({
    open,
    unit,
    onOpenChange,
}: Props) {
    const form = useForm({});

    const restoreUnit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!unit) {
            return;
        }

        form.patch(route('organization.units.restore', unit.id), {
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
                    <div className="flex items-start gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <RotateCcw className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>
                                Aktifkan kembali unit organisasi
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Unit akan dikembalikan ke status aktif agar
                                dapat digunakan lagi dalam operasional.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form onSubmit={restoreUnit} className="space-y-4">
                    <div className="rounded-xl border bg-muted/30 p-4">
                        <p className="text-sm font-medium">
                            {unit?.name ?? 'Unit organisasi'}
                        </p>
                        <p className="mt-1 text-xs text-foreground/60">
                            {unit?.code ?? '-'} · Status akan menjadi aktif.
                        </p>
                    </div>

                    {form.errors.unit ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            {form.errors.unit}
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
                        <LoadingButton type="submit" loading={form.processing}>
                            {form.processing
                                ? 'Mengaktifkan...'
                                : 'Aktifkan kembali'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
