import { useForm } from '@inertiajs/react';
import { BookOpen } from 'lucide-react';
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
import type { ClassGroupStatus } from '../types';
import {
    KelasRombelStatusField,
    KelasRombelTextareaField,
    KelasRombelTextField,
} from './KelasRombelFormFields';

type CurriculumPayload = {
    code: string;
    name: string;
    description: string;
    status: ClassGroupStatus;
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export function CurriculumFormDialog({ open, onOpenChange }: Props) {
    const form = useForm<CurriculumPayload>({
        code: '',
        name: '',
        description: '',
        status: 'active',
    });

    useEffect(() => {
        if (open) {
            form.reset();
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(
            String(
                routeOr(
                    '/academic/class-groups/curricula',
                    'academic.class-groups.curricula.store',
                ),
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
            onOpenChange={(nextOpen) => !form.processing && onOpenChange(nextOpen)}
        >
            <DialogContent className="max-h-[90vh] max-w-xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <BookOpen className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Tambah kurikulum</DialogTitle>
                            <DialogDescription className="mt-1">
                                Kurikulum dipakai sebagai acuan minimum untuk
                                rombel. Pemetaan mapel/detail kurikulum menyusul.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-[160px_1fr]">
                        <KelasRombelTextField
                            id="curriculum-code"
                            label="Kode"
                            value={form.data.code}
                            error={form.errors.code}
                            placeholder="KUR-MTS"
                            maxLength={40}
                            onChange={(value) => form.setData('code', value.toUpperCase())}
                        />
                        <KelasRombelTextField
                            id="curriculum-name"
                            label="Nama"
                            value={form.data.name}
                            error={form.errors.name}
                            placeholder="Kurikulum MTs"
                            maxLength={180}
                            onChange={(value) => form.setData('name', value)}
                        />
                    </div>
                    <KelasRombelTextareaField
                        id="curriculum-description"
                        label="Deskripsi"
                        value={form.data.description}
                        error={form.errors.description}
                        placeholder="Catatan ringkas kurikulum"
                        onChange={(value) => form.setData('description', value)}
                    />
                    <KelasRombelStatusField
                        value={form.data.status}
                        error={form.errors.status}
                        onChange={(value) => form.setData('status', value)}
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
                        <LoadingButton type="submit" loading={form.processing}>
                            {form.processing ? 'Menyimpan...' : 'Tambah kurikulum'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
