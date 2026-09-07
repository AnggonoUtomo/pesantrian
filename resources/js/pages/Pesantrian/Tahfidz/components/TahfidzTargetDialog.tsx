import { useForm } from '@inertiajs/react';
import { Target } from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { LoadingButton } from '@/components/ui/loading-button';
import { SelectItem } from '@/components/ui/select';
import { routeOr } from '@/lib/route';
import type {
    TahfidzIndexPageProps,
    TahfidzTargetOption,
    TahfidzTargetPayload,
} from '../types';
import {
    nullable,
    TahfidzCancelButton,
    TahfidzFieldError,
    TahfidzSelectField,
    TahfidzTextareaField,
    TahfidzTextField,
} from './TahfidzFormFields';

type Props = {
    open: boolean;
    options: TahfidzIndexPageProps['options'];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey = keyof TahfidzTargetPayload | 'payload' | 'target';

export function TahfidzTargetDialog({ open, options, onOpenChange }: Props) {
    const [selectedTargetId, setSelectedTargetId] = useState('new');
    const selectedTarget = useMemo(
        () =>
            options.targets.find((target) => target.id === selectedTargetId) ??
            null,
        [options.targets, selectedTargetId],
    );
    const form = useForm<TahfidzTargetPayload>(
        targetDefaults(options, selectedTarget),
    );
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const isEdit = selectedTarget !== null;

    const chooseTarget = (targetId: string) => {
        setSelectedTargetId(targetId);
        const target =
            options.targets.find((option) => option.id === targetId) ?? null;
        form.setData(targetDefaults(options, target));
        form.clearErrors();
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const url =
            selectedTarget === null
                ? routeOr(
                      '/pesantrian/tahfidz/targets',
                      'pesantrian.tahfidz.targets.store',
                  )
                : routeOr(
                      `/pesantrian/tahfidz/targets/${selectedTarget.id}`,
                      'pesantrian.tahfidz.targets.update',
                      selectedTarget.id,
                  );
        const submitOptions = {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedTargetId('new');
                form.reset();
                onOpenChange(false);
            },
        };

        if (selectedTarget === null) {
            form.post(url, submitOptions);

            return;
        }

        form.patch(url, submitOptions);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent className="max-h-[90vh] max-w-3xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <Target className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Target hafalan</DialogTitle>
                            <DialogDescription className="mt-1">
                                Target membantu pembimbing membaca rencana
                                hafalan santri pada periode berjalan.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {errors.payload ? <TahfidzFieldError message={errors.payload} /> : null}
                {errors.target ? <TahfidzFieldError message={errors.target} /> : null}

                <form className="space-y-4" onSubmit={submit}>
                    <TahfidzSelectField
                        id="tahfidz-target-existing"
                        label="Mode"
                        value={selectedTargetId}
                        onChange={chooseTarget}
                    >
                        <SelectItem value="new">Buat target baru</SelectItem>
                        {options.targets.map((target) => (
                            <SelectItem key={target.id} value={target.id}>
                                Edit {target.label}
                            </SelectItem>
                        ))}
                    </TahfidzSelectField>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <TahfidzSelectField
                            id="tahfidz-target-program"
                            label="Program"
                            value={form.data.program_id}
                            error={errors.program_id}
                            placeholder="Pilih program"
                            onChange={(value) =>
                                form.setData('program_id', value)
                            }
                        >
                            {options.programs.map((program) => (
                                <SelectItem key={program.id} value={program.id}>
                                    {program.name} ({program.code})
                                </SelectItem>
                            ))}
                        </TahfidzSelectField>
                        <TahfidzSelectField
                            id="tahfidz-target-student"
                            label="Santri"
                            value={form.data.student_id}
                            error={errors.student_id}
                            placeholder="Pilih santri"
                            onChange={(value) =>
                                form.setData('student_id', value)
                            }
                        >
                            {options.students.map((student) => (
                                <SelectItem key={student.id} value={student.id}>
                                    {student.name} ({student.code})
                                </SelectItem>
                            ))}
                        </TahfidzSelectField>
                        <TahfidzSelectField
                            id="tahfidz-target-period"
                            label="Periode"
                            value={form.data.academic_period_id ?? 'none'}
                            error={errors.academic_period_id}
                            onChange={(value) =>
                                form.setData(
                                    'academic_period_id',
                                    value === 'none' ? null : value,
                                )
                            }
                        >
                            <SelectItem value="none">Tanpa periode</SelectItem>
                            {options.academicPeriods.map((period) => (
                                <SelectItem key={period.id} value={period.id}>
                                    {period.label}
                                </SelectItem>
                            ))}
                        </TahfidzSelectField>
                        <TahfidzSelectField
                            id="tahfidz-target-status"
                            label="Status target"
                            value={form.data.status}
                            error={errors.status}
                            onChange={(value) =>
                                form.setData(
                                    'status',
                                    value as TahfidzTargetPayload['status'],
                                )
                            }
                        >
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="completed">Selesai</SelectItem>
                            <SelectItem value="cancelled">Dibatalkan</SelectItem>
                        </TahfidzSelectField>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-4">
                        <TahfidzTextField
                            id="tahfidz-target-juz"
                            label="Target juz"
                            type="number"
                            value={form.data.target_juz ?? ''}
                            error={errors.target_juz}
                            onChange={(value) =>
                                form.setData('target_juz', nullable(value))
                            }
                        />
                        <TahfidzTextField
                            id="tahfidz-target-surah"
                            label="Target surah"
                            value={form.data.target_surah ?? ''}
                            error={errors.target_surah}
                            placeholder="Contoh: Al-Baqarah"
                            onChange={(value) =>
                                form.setData('target_surah', nullable(value))
                            }
                        />
                        <TahfidzTextField
                            id="tahfidz-target-ayah-from"
                            label="Ayat mulai"
                            type="number"
                            value={form.data.target_ayah_from ?? ''}
                            error={errors.target_ayah_from}
                            onChange={(value) =>
                                form.setData('target_ayah_from', nullable(value))
                            }
                        />
                        <TahfidzTextField
                            id="tahfidz-target-ayah-to"
                            label="Ayat selesai"
                            type="number"
                            value={form.data.target_ayah_to ?? ''}
                            error={errors.target_ayah_to}
                            onChange={(value) =>
                                form.setData('target_ayah_to', nullable(value))
                            }
                        />
                    </div>

                    <TahfidzTextareaField
                        id="tahfidz-target-note"
                        label="Catatan target"
                        value={form.data.target_note ?? ''}
                        error={errors.target_note}
                        placeholder="Opsional"
                        onChange={(value) =>
                            form.setData('target_note', nullable(value))
                        }
                    />

                    <DialogFooter>
                        <TahfidzCancelButton
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        />
                        <LoadingButton type="submit" loading={form.processing}>
                            {isEdit ? 'Simpan target' : 'Buat target'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function targetDefaults(
    options: TahfidzIndexPageProps['options'],
    target: TahfidzTargetOption | null,
): TahfidzTargetPayload {
    return {
        program_id: target?.program_id ?? options.programs[0]?.id ?? '',
        student_id: target?.student_id ?? options.students[0]?.id ?? '',
        academic_period_id:
            target?.academic_period_id ?? options.academicPeriods[0]?.id ?? null,
        target_juz: target?.target_juz === null ? null : String(target?.target_juz ?? ''),
        target_surah: target?.target_surah ?? null,
        target_ayah_from:
            target?.target_ayah_from === null
                ? null
                : String(target?.target_ayah_from ?? ''),
        target_ayah_to:
            target?.target_ayah_to === null
                ? null
                : String(target?.target_ayah_to ?? ''),
        target_note: target?.target_note ?? null,
        status:
            (target?.status as TahfidzTargetPayload['status']) ?? 'active',
    };
}
