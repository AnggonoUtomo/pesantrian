import { useForm } from '@inertiajs/react';
import { CalendarRange, PencilLine } from 'lucide-react';
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
import route from '@/lib/route';
import type { AcademicPeriodStatus, AcademicYear } from '../types';
import {
    AcademicPeriodStatusField,
    AcademicPeriodTextField,
} from './AcademicPeriodFormFields';

type AcademicYearFormData = {
    code: string;
    name: string;
    starts_on: string;
    ends_on: string;
    status: AcademicPeriodStatus;
};

type AcademicYearFormDialogProps = {
    open: boolean;
    year: AcademicYear | null;
    onOpenChange: (open: boolean) => void;
};

export function AcademicYearFormDialog({
    open,
    year,
    onOpenChange,
}: AcademicYearFormDialogProps) {
    const isEdit = year !== null;
    const form = useForm<AcademicYearFormData>(yearFormDefaults(year));

    useEffect(() => {
        if (open) {
            form.setData(yearFormDefaults(year));
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, year?.id]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (isEdit && year) {
            form.put(route('academic.periods.years.update', year.id), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });

            return;
        }

        form.post(route('academic.periods.years.store'), {
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
            <DialogContent className="max-h-[90vh] max-w-xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine className="size-5" />
                            ) : (
                                <CalendarRange className="size-5" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit tahun akademik'
                                    : 'Tambah tahun akademik'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Tahun akademik menjadi rentang besar untuk term
                                atau semester operasional.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-[160px_1fr]">
                        <AcademicPeriodTextField
                            id="academic-year-code"
                            label="Kode"
                            value={form.data.code}
                            error={form.errors.code}
                            placeholder="2027-2028"
                            maxLength={40}
                            onChange={(value) =>
                                form.setData('code', value.toUpperCase())
                            }
                        />
                        <AcademicPeriodTextField
                            id="academic-year-name"
                            label="Nama"
                            value={form.data.name}
                            error={form.errors.name}
                            placeholder="Tahun Akademik 2027/2028"
                            maxLength={180}
                            onChange={(value) => form.setData('name', value)}
                        />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-3">
                        <AcademicPeriodTextField
                            id="academic-year-starts-on"
                            label="Mulai"
                            type="date"
                            value={form.data.starts_on}
                            error={form.errors.starts_on}
                            onChange={(value) =>
                                form.setData('starts_on', value)
                            }
                        />
                        <AcademicPeriodTextField
                            id="academic-year-ends-on"
                            label="Selesai"
                            type="date"
                            value={form.data.ends_on}
                            error={form.errors.ends_on}
                            onChange={(value) =>
                                form.setData('ends_on', value)
                            }
                        />
                        <AcademicPeriodStatusField
                            value={form.data.status}
                            error={form.errors.status}
                            onChange={(value) => form.setData('status', value)}
                        />
                    </div>
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
                                ? 'Menyimpan...'
                                : isEdit
                                  ? 'Simpan perubahan'
                                  : 'Tambah tahun'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function yearFormDefaults(year: AcademicYear | null): AcademicYearFormData {
    return {
        code: year?.code ?? '',
        name: year?.name ?? '',
        starts_on: year?.starts_on ?? '',
        ends_on: year?.ends_on ?? '',
        status: year?.status ?? 'draft',
    };
}
