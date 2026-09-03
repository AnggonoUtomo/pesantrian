import { useForm } from '@inertiajs/react';
import { Layers3 } from 'lucide-react';
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
import type { ClassGroupStatus, ReferenceOption } from '../types';
import {
    KelasRombelSelectField,
    KelasRombelStatusField,
    KelasRombelTextField,
} from './KelasRombelFormFields';

type ClassLevelPayload = {
    unit_id: string;
    code: string;
    name: string;
    sequence: string;
    status: ClassGroupStatus;
};

type Props = {
    open: boolean;
    units: ReferenceOption[];
    onOpenChange: (open: boolean) => void;
};

export function ClassLevelFormDialog({ open, units, onOpenChange }: Props) {
    const form = useForm<ClassLevelPayload>({
        unit_id: '',
        code: '',
        name: '',
        sequence: '',
        status: 'active',
    });

    useEffect(() => {
        if (open) {
            form.setData({
                unit_id: units[0]?.id ?? '',
                code: '',
                name: '',
                sequence: '',
                status: 'active',
            });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, units]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(
            String(
                routeOr(
                    '/academic/class-groups/levels',
                    'academic.class-groups.levels.store',
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
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            <Layers3 className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Tambah tingkat kelas</DialogTitle>
                            <DialogDescription className="mt-1">
                                Tingkat kelas menjadi jenjang seperti VII, VIII,
                                IX, X, atau level lain sesuai unit pendidikan.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <KelasRombelSelectField
                        id="class-level-unit"
                        label="Unit"
                        value={form.data.unit_id}
                        error={form.errors.unit_id}
                        options={units}
                        placeholder="Pilih unit"
                        onChange={(value) => form.setData('unit_id', value)}
                    />
                    <div className="grid gap-4 sm:grid-cols-[160px_1fr_120px]">
                        <KelasRombelTextField
                            id="class-level-code"
                            label="Kode"
                            value={form.data.code}
                            error={form.errors.code}
                            placeholder="VII"
                            maxLength={40}
                            onChange={(value) => form.setData('code', value.toUpperCase())}
                        />
                        <KelasRombelTextField
                            id="class-level-name"
                            label="Nama"
                            value={form.data.name}
                            error={form.errors.name}
                            placeholder="Kelas VII"
                            maxLength={180}
                            onChange={(value) => form.setData('name', value)}
                        />
                        <KelasRombelTextField
                            id="class-level-sequence"
                            label="Urutan"
                            type="number"
                            value={form.data.sequence}
                            error={form.errors.sequence}
                            placeholder="7"
                            onChange={(value) => form.setData('sequence', value)}
                        />
                    </div>
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
                            {form.processing ? 'Menyimpan...' : 'Tambah kelas'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
