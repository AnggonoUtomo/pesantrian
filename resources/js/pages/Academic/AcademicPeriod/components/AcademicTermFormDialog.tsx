import { useForm } from '@inertiajs/react';
import { Layers3, PencilLine } from 'lucide-react';
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
import type { AcademicPeriodStatus, AcademicTerm, AcademicYear } from '../types';
import {
    AcademicPeriodStatusField,
    AcademicPeriodTextField,
    AcademicYearSelectField,
} from './AcademicPeriodFormFields';

type AcademicTermFormData = {
    academic_year_id: string;
    code: string;
    name: string;
    sequence: number;
    starts_on: string;
    ends_on: string;
    status: AcademicPeriodStatus;
};

type AcademicTermFormDialogProps = {
    open: boolean;
    term: AcademicTerm | null;
    years: AcademicYear[];
    onOpenChange: (open: boolean) => void;
};

export function AcademicTermFormDialog({
    open,
    term,
    years,
    onOpenChange,
}: AcademicTermFormDialogProps) {
    const isEdit = term !== null;
    const form = useForm<AcademicTermFormData>(termFormDefaults(term, years));

    useEffect(() => {
        if (open) {
            form.setData(termFormDefaults(term, years));
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, term?.id, years]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (isEdit && term) {
            form.put(route('academic.periods.terms.update', term.id), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });

            return;
        }

        form.post(route('academic.periods.terms.store'), {
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
            <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine className="size-5" />
                            ) : (
                                <Layers3 className="size-5" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit term akademik'
                                    : 'Tambah term akademik'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Term adalah semester atau pembagian periode di
                                dalam satu tahun akademik.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <AcademicYearSelectField
                        years={years}
                        value={form.data.academic_year_id}
                        error={form.errors.academic_year_id}
                        onChange={(value) =>
                            form.setData('academic_year_id', value)
                        }
                    />
                    <div className="grid gap-4 sm:grid-cols-[1fr_120px]">
                        <AcademicPeriodTextField
                            id="academic-term-code"
                            label="Kode term"
                            value={form.data.code}
                            error={form.errors.code}
                            placeholder="2027-2028-GANJIL"
                            maxLength={60}
                            onChange={(value) =>
                                form.setData('code', value.toUpperCase())
                            }
                        />
                        <AcademicPeriodTextField
                            id="academic-term-sequence"
                            label="Urutan"
                            type="number"
                            value={String(form.data.sequence)}
                            error={form.errors.sequence}
                            min={1}
                            max={20}
                            onChange={(value) =>
                                form.setData('sequence', Number(value))
                            }
                        />
                    </div>
                    <AcademicPeriodTextField
                        id="academic-term-name"
                        label="Nama term"
                        value={form.data.name}
                        error={form.errors.name}
                        placeholder="Semester Ganjil"
                        maxLength={180}
                        onChange={(value) => form.setData('name', value)}
                    />
                    <div className="grid gap-4 sm:grid-cols-3">
                        <AcademicPeriodTextField
                            id="academic-term-starts-on"
                            label="Mulai"
                            type="date"
                            value={form.data.starts_on}
                            error={form.errors.starts_on}
                            onChange={(value) =>
                                form.setData('starts_on', value)
                            }
                        />
                        <AcademicPeriodTextField
                            id="academic-term-ends-on"
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
                                  : 'Tambah term'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function termFormDefaults(
    term: AcademicTerm | null,
    years: AcademicYear[],
): AcademicTermFormData {
    return {
        academic_year_id: term?.academic_year_id ?? years[0]?.id ?? '',
        code: term?.code ?? '',
        name: term?.name ?? '',
        sequence: term?.sequence ?? 1,
        starts_on: term?.starts_on ?? '',
        ends_on: term?.ends_on ?? '',
        status: term?.status ?? 'draft',
    };
}
