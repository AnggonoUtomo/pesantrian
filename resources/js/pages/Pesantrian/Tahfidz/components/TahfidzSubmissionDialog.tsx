import { useForm } from '@inertiajs/react';
import { ClipboardPlus, PencilLine } from 'lucide-react';
import type { FormEvent } from 'react';
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
    TahfidzSubmission,
    TahfidzSubmissionPayload,
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
    submission: TahfidzSubmission | null;
    options: TahfidzIndexPageProps['options'];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey = keyof TahfidzSubmissionPayload | 'payload' | 'hafalan';

export function TahfidzSubmissionDialog({
    open,
    submission,
    options,
    onOpenChange,
}: Props) {
    const form = useForm<TahfidzSubmissionPayload>(
        submissionDefaults(options, submission),
    );
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const isEdit = submission !== null;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const url =
            submission === null
                ? routeOr(
                      '/pesantrian/tahfidz/submissions',
                      'pesantrian.tahfidz.submissions.store',
                  )
                : routeOr(
                      `/pesantrian/tahfidz/submissions/${submission.id}`,
                      'pesantrian.tahfidz.submissions.update',
                      submission.id,
                  );
        const submitOptions = {
            preserveScroll: true,
            onSuccess: () => {
                if (!isEdit) {
                    form.reset();
                }

                onOpenChange(false);
            },
        };

        if (submission === null) {
            form.post(url, submitOptions);

            return;
        }

        form.patch(url, submitOptions);
    };

    const filteredTargets = options.targets.filter(
        (target) =>
            target.program_id === form.data.program_id &&
            target.student_id === form.data.student_id,
    );

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent className="max-h-[90vh] max-w-4xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine className="size-5" aria-hidden="true" />
                            ) : (
                                <ClipboardPlus className="size-5" aria-hidden="true" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit ? 'Edit setoran tahfidz' : 'Tambah setoran tahfidz'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Catat hafalan baru atau murojaah santri. Status
                                final tetap lewat proses review.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {errors.payload ? <TahfidzFieldError message={errors.payload} /> : null}
                {errors.hafalan ? <TahfidzFieldError message={errors.hafalan} /> : null}

                <form className="space-y-4" onSubmit={submit}>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <TahfidzSelectField
                            id="tahfidz-submission-program"
                            label="Program"
                            value={form.data.program_id}
                            error={errors.program_id}
                            placeholder="Pilih program"
                            onChange={(value) => {
                                form.setData((data) => ({
                                    ...data,
                                    program_id: value,
                                    target_id: null,
                                }));
                            }}
                        >
                            {options.programs.map((program) => (
                                <SelectItem key={program.id} value={program.id}>
                                    {program.name} ({program.code})
                                </SelectItem>
                            ))}
                        </TahfidzSelectField>
                        <TahfidzSelectField
                            id="tahfidz-submission-student"
                            label="Santri"
                            value={form.data.student_id}
                            error={errors.student_id}
                            placeholder="Pilih santri"
                            onChange={(value) => {
                                form.setData((data) => ({
                                    ...data,
                                    student_id: value,
                                    target_id: null,
                                }));
                            }}
                        >
                            {options.students.map((student) => (
                                <SelectItem key={student.id} value={student.id}>
                                    {student.name} ({student.code})
                                </SelectItem>
                            ))}
                        </TahfidzSelectField>
                        <TahfidzSelectField
                            id="tahfidz-submission-target"
                            label="Target hafalan"
                            value={form.data.target_id ?? 'none'}
                            error={errors.target_id}
                            onChange={(value) =>
                                form.setData(
                                    'target_id',
                                    value === 'none' ? null : value,
                                )
                            }
                        >
                            <SelectItem value="none">Tanpa target</SelectItem>
                            {filteredTargets.map((target) => (
                                <SelectItem key={target.id} value={target.id}>
                                    {target.label}
                                </SelectItem>
                            ))}
                        </TahfidzSelectField>
                        <TahfidzSelectField
                            id="tahfidz-submission-supervisor"
                            label="Pembimbing"
                            value={form.data.supervisor_id ?? 'none'}
                            error={errors.supervisor_id}
                            onChange={(value) =>
                                form.setData(
                                    'supervisor_id',
                                    value === 'none' ? null : value,
                                )
                            }
                        >
                            <SelectItem value="none">Tanpa pembimbing</SelectItem>
                            {options.employees.map((employee) => (
                                <SelectItem key={employee.id} value={employee.id}>
                                    {employee.name} ({employee.code})
                                </SelectItem>
                            ))}
                        </TahfidzSelectField>
                        <TahfidzTextField
                            id="tahfidz-submission-date"
                            label="Tanggal setoran"
                            type="date"
                            value={form.data.submission_date}
                            error={errors.submission_date}
                            required
                            onChange={(value) =>
                                form.setData('submission_date', value)
                            }
                        />
                        <TahfidzSelectField
                            id="tahfidz-submission-type"
                            label="Tipe setoran"
                            value={form.data.type}
                            error={errors.type}
                            onChange={(value) =>
                                form.setData(
                                    'type',
                                    value as TahfidzSubmissionPayload['type'],
                                )
                            }
                        >
                            <SelectItem value="new_memorization">
                                Hafalan baru
                            </SelectItem>
                            <SelectItem value="murojaah">Murojaah</SelectItem>
                        </TahfidzSelectField>
                        <TahfidzSelectField
                            id="tahfidz-submission-status"
                            label="Status awal"
                            value={form.data.status}
                            error={errors.status}
                            onChange={(value) =>
                                form.setData(
                                    'status',
                                    value as TahfidzSubmissionPayload['status'],
                                )
                            }
                        >
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="submitted">
                                Menunggu review
                            </SelectItem>
                        </TahfidzSelectField>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-4">
                        <TahfidzTextField
                            id="tahfidz-submission-juz"
                            label="Juz"
                            type="number"
                            value={form.data.juz ?? ''}
                            error={errors.juz}
                            onChange={(value) =>
                                form.setData('juz', nullable(value))
                            }
                        />
                        <TahfidzTextField
                            id="tahfidz-submission-surah"
                            label="Surah"
                            value={form.data.surah ?? ''}
                            error={errors.surah}
                            placeholder="Contoh: Al-Baqarah"
                            onChange={(value) =>
                                form.setData('surah', nullable(value))
                            }
                        />
                        <TahfidzTextField
                            id="tahfidz-submission-ayah-from"
                            label="Ayat mulai"
                            type="number"
                            value={form.data.ayah_from ?? ''}
                            error={errors.ayah_from}
                            onChange={(value) =>
                                form.setData('ayah_from', nullable(value))
                            }
                        />
                        <TahfidzTextField
                            id="tahfidz-submission-ayah-to"
                            label="Ayat selesai"
                            type="number"
                            value={form.data.ayah_to ?? ''}
                            error={errors.ayah_to}
                            onChange={(value) =>
                                form.setData('ayah_to', nullable(value))
                            }
                        />
                    </div>

                    <TahfidzTextareaField
                        id="tahfidz-submission-quality-note"
                        label="Catatan kualitas"
                        value={form.data.quality_note ?? ''}
                        error={errors.quality_note}
                        placeholder="Contoh: Lancar, perlu ulang ayat akhir, atau catatan tajwid ringkas"
                        onChange={(value) =>
                            form.setData('quality_note', nullable(value))
                        }
                    />

                    <DialogFooter>
                        <TahfidzCancelButton
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        />
                        <LoadingButton type="submit" loading={form.processing}>
                            {isEdit ? 'Simpan setoran' : 'Buat setoran'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function submissionDefaults(
    options: TahfidzIndexPageProps['options'],
    submission: TahfidzSubmission | null,
): TahfidzSubmissionPayload {
    return {
        program_id: submission?.program.id ?? options.programs[0]?.id ?? '',
        target_id: submission?.target?.id ?? null,
        student_id: submission?.student_id ?? options.students[0]?.id ?? '',
        supervisor_id: submission?.supervisor_id ?? null,
        submission_date:
            submission?.submission_date ?? new Date().toISOString().slice(0, 10),
        type: submission?.type ?? 'new_memorization',
        juz: submission?.juz === null ? null : String(submission?.juz ?? ''),
        surah: submission?.surah ?? null,
        ayah_from:
            submission?.ayah_from === null
                ? null
                : String(submission?.ayah_from ?? ''),
        ayah_to:
            submission?.ayah_to === null ? null : String(submission?.ayah_to ?? ''),
        status:
            submission?.status === 'submitted' || submission?.status === 'draft'
                ? submission.status
                : 'draft',
        quality_note: submission?.quality_note ?? null,
    };
}
