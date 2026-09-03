import { useForm } from '@inertiajs/react';
import { School } from 'lucide-react';
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

type ClassGroupPayload = {
    academic_year_id: string;
    academic_term_id: string;
    unit_id: string;
    curriculum_id: string;
    class_level_id: string;
    code: string;
    name: string;
    capacity: string;
    status: ClassGroupStatus;
};

type Props = {
    open: boolean;
    options: {
        academicYears: ReferenceOption[];
        academicTerms: ReferenceOption[];
        units: ReferenceOption[];
        curricula: ReferenceOption[];
        classLevels: ReferenceOption[];
    };
    onOpenChange: (open: boolean) => void;
};

export function ClassGroupFormDialog({ open, options, onOpenChange }: Props) {
    const form = useForm<ClassGroupPayload>(defaults(options));

    useEffect(() => {
        if (open) {
            form.setData(defaults(options));
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, options]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            curriculum_id:
                data.curriculum_id === '__none' ? null : data.curriculum_id,
            capacity: data.capacity === '' ? null : Number(data.capacity),
        }));
        form.post(
            String(routeOr('/academic/class-groups', 'academic.class-groups.store')),
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
            <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--purple flex size-10 items-center justify-center rounded-lg">
                            <School className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Tambah rombel</DialogTitle>
                            <DialogDescription className="mt-1">
                                Rombel selalu terikat tahun akademik, semester,
                                unit, dan tingkat kelas.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-4 md:grid-cols-2">
                        <KelasRombelSelectField
                            id="class-group-year"
                            label="Tahun ajaran"
                            value={form.data.academic_year_id}
                            error={form.errors.academic_year_id}
                            options={options.academicYears}
                            onChange={(value) => form.setData('academic_year_id', value)}
                        />
                        <KelasRombelSelectField
                            id="class-group-term"
                            label="Semester"
                            value={form.data.academic_term_id}
                            error={form.errors.academic_term_id}
                            options={options.academicTerms}
                            onChange={(value) => form.setData('academic_term_id', value)}
                        />
                        <KelasRombelSelectField
                            id="class-group-unit"
                            label="Unit"
                            value={form.data.unit_id}
                            error={form.errors.unit_id}
                            options={options.units}
                            onChange={(value) => form.setData('unit_id', value)}
                        />
                        <KelasRombelSelectField
                            id="class-group-level"
                            label="Tingkat kelas"
                            value={form.data.class_level_id}
                            error={form.errors.class_level_id}
                            options={options.classLevels}
                            onChange={(value) => form.setData('class_level_id', value)}
                        />
                    </div>
                    <KelasRombelSelectField
                        id="class-group-curriculum"
                        label="Kurikulum"
                        value={form.data.curriculum_id}
                        error={form.errors.curriculum_id}
                        options={options.curricula}
                        nullableLabel="Tanpa kurikulum dulu"
                        onChange={(value) => form.setData('curriculum_id', value)}
                    />
                    <div className="grid gap-4 sm:grid-cols-[160px_1fr_120px]">
                        <KelasRombelTextField
                            id="class-group-code"
                            label="Kode"
                            value={form.data.code}
                            error={form.errors.code}
                            placeholder="VII-A"
                            maxLength={40}
                            onChange={(value) => form.setData('code', value.toUpperCase())}
                        />
                        <KelasRombelTextField
                            id="class-group-name"
                            label="Nama"
                            value={form.data.name}
                            error={form.errors.name}
                            placeholder="Kelas VII A"
                            maxLength={180}
                            onChange={(value) => form.setData('name', value)}
                        />
                        <KelasRombelTextField
                            id="class-group-capacity"
                            label="Kapasitas"
                            type="number"
                            value={form.data.capacity}
                            error={form.errors.capacity}
                            placeholder="32"
                            onChange={(value) => form.setData('capacity', value)}
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
                            {form.processing ? 'Menyimpan...' : 'Tambah rombel'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function defaults(options: Props['options']): ClassGroupPayload {
    return {
        academic_year_id: options.academicYears[0]?.id ?? '',
        academic_term_id: options.academicTerms[0]?.id ?? '',
        unit_id: options.units[0]?.id ?? '',
        curriculum_id: options.curricula[0]?.id ?? '__none',
        class_level_id: options.classLevels[0]?.id ?? '',
        code: '',
        name: '',
        capacity: '',
        status: 'active',
    };
}
