import { useForm } from '@inertiajs/react';
import { ClipboardPlus, PencilLine } from 'lucide-react';
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
import { SelectItem } from '@/components/ui/select';
import { routeOr } from '@/lib/route';
import type {
    StudentDisciplineCase,
    StudentDisciplineIndexPageProps,
    StudentDisciplineMutationPayload,
    StudentDisciplineSeverity,
} from '../types';
import {
    fromDateTimeLocal,
    KedisiplinanFieldError,
    KedisiplinanSelectField,
    KedisiplinanTextareaField,
    KedisiplinanTextField,
    nullable,
    toDateTimeLocal,
} from './KedisiplinanSantriFormFields';

type Props = {
    open: boolean;
    case: StudentDisciplineCase | null;
    options: StudentDisciplineIndexPageProps['options'];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey =
    | keyof StudentDisciplineMutationPayload
    | 'student_id'
    | 'category_id'
    | 'status';

export function KedisiplinanSantriMutationDialog({
    open,
    case: disciplineCase,
    options,
    onOpenChange,
}: Props) {
    const form = useForm<StudentDisciplineMutationPayload>(
        caseDefaults(disciplineCase, options),
    );
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const isEdit = disciplineCase !== null;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const payload = {
            ...form.data,
            student_id:
                form.data.student_id === 'none' ? '' : form.data.student_id,
            category_id:
                form.data.category_id === 'none' ? '' : form.data.category_id,
            assigned_employee_id:
                form.data.assigned_employee_id === 'none'
                    ? null
                    : form.data.assigned_employee_id,
            points:
                form.data.points === '' || form.data.points === null
                    ? null
                    : Number(form.data.points),
            occurred_at: fromDateTimeLocal(form.data.occurred_at),
            location: nullable(form.data.location ?? ''),
            revision_reason: isEdit
                ? form.data.revision_reason || 'Koreksi data kasus dari UI.'
                : undefined,
        };
        const url =
            disciplineCase === null
                ? routeOr(
                      '/pesantrian/student-discipline-cases',
                      'pesantrian.student-discipline-cases.store',
                  )
                : routeOr(
                      `/pesantrian/student-discipline-cases/${disciplineCase.id}`,
                      'pesantrian.student-discipline-cases.update',
                      disciplineCase.id,
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

        form.transform(() => payload);

        if (disciplineCase === null) {
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
                        <span className="dashboard-icon dashboard-accent--amber flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            ) : (
                                <ClipboardPlus
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit kasus kedisiplinan'
                                    : 'Buat kasus kedisiplinan'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Simpan sebagai draft dulu. Submit dan proses
                                pembinaan dilakukan lewat aksi lifecycle.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <KedisiplinanFieldError message={errors.status} />
                    ) : null}
                    <div className="grid gap-4 sm:grid-cols-2">
                        <KedisiplinanSelectField
                            id="student-discipline-student"
                            label="Santri"
                            value={form.data.student_id}
                            error={errors.student_id}
                            placeholder="Pilih santri"
                            onChange={(value) =>
                                form.setData('student_id', value)
                            }
                        >
                            {options.students.length > 0 ? (
                                options.students.map((student) => (
                                    <SelectItem
                                        key={student.value}
                                        value={student.value}
                                    >
                                        {student.label}
                                    </SelectItem>
                                ))
                            ) : (
                                <SelectItem value="none">
                                    Belum ada santri aktif
                                </SelectItem>
                            )}
                        </KedisiplinanSelectField>
                        <KedisiplinanSelectField
                            id="student-discipline-category"
                            label="Kategori"
                            value={form.data.category_id}
                            error={errors.category_id}
                            placeholder="Pilih kategori"
                            onChange={(value) =>
                                form.setData('category_id', value)
                            }
                        >
                            {options.categories.length > 0 ? (
                                options.categories.map((category) => (
                                    <SelectItem
                                        key={category.value}
                                        value={category.value}
                                    >
                                        {category.label}
                                    </SelectItem>
                                ))
                            ) : (
                                <SelectItem value="none">
                                    Belum ada kategori aktif
                                </SelectItem>
                            )}
                        </KedisiplinanSelectField>
                        <KedisiplinanSelectField
                            id="student-discipline-severity"
                            label="Tingkat pelanggaran"
                            value={form.data.severity}
                            error={errors.severity}
                            onChange={(value) =>
                                form.setData(
                                    'severity',
                                    value as StudentDisciplineSeverity,
                                )
                            }
                        >
                            {options.severities.map((severity) => (
                                <SelectItem
                                    key={severity.value}
                                    value={severity.value}
                                >
                                    {severity.label}
                                </SelectItem>
                            ))}
                        </KedisiplinanSelectField>
                        <KedisiplinanTextField
                            id="student-discipline-points"
                            label="Poin"
                            type="number"
                            value={String(form.data.points ?? '')}
                            error={errors.points}
                            placeholder="Opsional"
                            onChange={(value) => form.setData('points', value)}
                        />
                        <KedisiplinanTextField
                            id="student-discipline-occurred-at"
                            label="Waktu kejadian"
                            type="datetime-local"
                            value={form.data.occurred_at}
                            error={errors.occurred_at}
                            required
                            onChange={(value) =>
                                form.setData('occurred_at', value)
                            }
                        />
                        <KedisiplinanTextField
                            id="student-discipline-location"
                            label="Lokasi"
                            value={form.data.location ?? ''}
                            error={errors.location}
                            placeholder="Contoh: masjid, kelas, asrama"
                            onChange={(value) =>
                                form.setData('location', nullable(value))
                            }
                        />
                        <KedisiplinanSelectField
                            id="student-discipline-officer"
                            label="Pembina/Petugas"
                            value={form.data.assigned_employee_id ?? 'none'}
                            error={errors.assigned_employee_id}
                            onChange={(value) =>
                                form.setData(
                                    'assigned_employee_id',
                                    value === 'none' ? null : value,
                                )
                            }
                        >
                            <SelectItem value="none">
                                Belum ditetapkan
                            </SelectItem>
                            {options.officers.map((officer) => (
                                <SelectItem
                                    key={officer.value}
                                    value={officer.value}
                                >
                                    {officer.label}
                                </SelectItem>
                            ))}
                        </KedisiplinanSelectField>
                    </div>
                    <KedisiplinanTextareaField
                        id="student-discipline-description"
                        label="Deskripsi kejadian"
                        value={form.data.description}
                        error={errors.description}
                        placeholder="Tuliskan kronologi singkat dan jelas"
                        required
                        onChange={(value) => form.setData('description', value)}
                    />
                    {isEdit ? (
                        <KedisiplinanTextareaField
                            id="student-discipline-revision-reason"
                            label="Alasan koreksi"
                            value={form.data.revision_reason ?? ''}
                            error={errors.revision_reason}
                            placeholder="Contoh: koreksi lokasi atau kronologi"
                            required
                            onChange={(value) =>
                                form.setData('revision_reason', value)
                            }
                        />
                    ) : null}
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
                            {isEdit ? 'Simpan perubahan' : 'Buat draft kasus'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function caseDefaults(
    disciplineCase: StudentDisciplineCase | null,
    options: StudentDisciplineIndexPageProps['options'],
): StudentDisciplineMutationPayload {
    return {
        student_id:
            disciplineCase?.student_id ?? options.students[0]?.value ?? 'none',
        category_id:
            disciplineCase?.category.id ??
            options.categories[0]?.value ??
            'none',
        severity: disciplineCase?.severity ?? 'minor',
        points: disciplineCase?.points ?? '',
        occurred_at: toDateTimeLocal(disciplineCase?.occurred_at),
        location: disciplineCase?.location ?? null,
        description: disciplineCase?.description ?? '',
        assigned_employee_id: disciplineCase?.assigned_employee_id ?? null,
        revision_reason: '',
    };
}
