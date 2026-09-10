import { useForm } from '@inertiajs/react';
import { Award, PencilLine } from 'lucide-react';
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
    AchievementLevel,
    AchievementType,
    StudentAchievement,
    StudentAchievementIndexPageProps,
    StudentAchievementMutationPayload,
} from '../types';
import {
    nullable,
    PrestasiFieldError,
    PrestasiSelectField,
    PrestasiTextareaField,
    PrestasiTextField,
} from './PrestasiSantriFormFields';

type Props = {
    open: boolean;
    achievement: StudentAchievement | null;
    options: StudentAchievementIndexPageProps['options'];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey =
    | keyof StudentAchievementMutationPayload
    | 'student_id'
    | 'category_id'
    | 'status';

export function PrestasiSantriMutationDialog({
    open,
    achievement,
    options,
    onOpenChange,
}: Props) {
    const form = useForm<StudentAchievementMutationPayload>(
        achievementDefaults(achievement, options),
    );
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const isEdit = achievement !== null;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            student_id: data.student_id === 'none' ? '' : data.student_id,
            category_id: data.category_id === 'none' ? '' : data.category_id,
            academic_period_id:
                data.academic_period_id === 'none'
                    ? null
                    : data.academic_period_id,
            mentor_employee_id:
                data.mentor_employee_id === 'none'
                    ? null
                    : data.mentor_employee_id,
            title: data.title.trim(),
            result: data.result.trim(),
            organizer: nullable(data.organizer ?? ''),
            event_name: nullable(data.event_name ?? ''),
            event_location: nullable(data.event_location ?? ''),
            achieved_on: data.achieved_on || null,
            period_started_on: data.period_started_on || null,
            period_ended_on: data.period_ended_on || null,
            description: nullable(data.description ?? ''),
            notes: nullable(data.notes ?? ''),
            revision_reason: isEdit
                ? data.revision_reason || 'Koreksi data prestasi dari UI.'
                : undefined,
        }));

        const submitOptions = {
            preserveScroll: true,
            onSuccess: () => {
                if (!isEdit) {
                    form.reset();
                }

                onOpenChange(false);
            },
        };

        if (achievement === null) {
            form.post(
                routeOr(
                    '/pesantrian/prestasi-santri',
                    'pesantrian.prestasi-santri.store',
                ),
                submitOptions,
            );

            return;
        }

        form.patch(
            routeOr(
                `/pesantrian/prestasi-santri/${achievement.id}`,
                'pesantrian.prestasi-santri.update',
                achievement.id,
            ),
            submitOptions,
        );
    };

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
                        <span className="dashboard-icon dashboard-accent--yellow flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            ) : (
                                <Award className="size-5" aria-hidden="true" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit draft prestasi'
                                    : 'Buat draft prestasi'}
                            </DialogTitle>
                            <DialogDescription>
                                Simpan sebagai draft. Submit dan verifikasi
                                dilakukan lewat aksi lifecycle.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <PrestasiFieldError message={errors.status} />
                    ) : null}
                    <div className="grid gap-4 sm:grid-cols-2">
                        <PrestasiSelectField
                            id="student-achievement-student"
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
                        </PrestasiSelectField>
                        <PrestasiSelectField
                            id="student-achievement-category"
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
                        </PrestasiSelectField>
                        <PrestasiSelectField
                            id="student-achievement-type"
                            label="Jenis prestasi"
                            value={form.data.achievement_type}
                            error={errors.achievement_type}
                            onChange={(value) =>
                                form.setData(
                                    'achievement_type',
                                    value as AchievementType,
                                )
                            }
                        >
                            {options.types.map((type) => (
                                <SelectItem key={type.value} value={type.value}>
                                    {type.label}
                                </SelectItem>
                            ))}
                        </PrestasiSelectField>
                        <PrestasiSelectField
                            id="student-achievement-level"
                            label="Tingkat"
                            value={form.data.level}
                            error={errors.level}
                            onChange={(value) =>
                                form.setData('level', value as AchievementLevel)
                            }
                        >
                            {options.levels.map((level) => (
                                <SelectItem
                                    key={level.value}
                                    value={level.value}
                                >
                                    {level.label}
                                </SelectItem>
                            ))}
                        </PrestasiSelectField>
                        <PrestasiTextField
                            id="student-achievement-title"
                            label="Judul prestasi"
                            value={form.data.title}
                            error={errors.title}
                            placeholder="Contoh: Juara Olimpiade Matematika"
                            required
                            onChange={(value) => form.setData('title', value)}
                        />
                        <PrestasiTextField
                            id="student-achievement-result"
                            label="Hasil"
                            value={form.data.result}
                            error={errors.result}
                            placeholder="Contoh: Juara 1"
                            required
                            onChange={(value) => form.setData('result', value)}
                        />
                        <PrestasiTextField
                            id="student-achievement-achieved-on"
                            label="Tanggal prestasi"
                            type="date"
                            value={form.data.achieved_on}
                            error={errors.achieved_on}
                            onChange={(value) =>
                                form.setData('achieved_on', value)
                            }
                        />
                        <PrestasiSelectField
                            id="student-achievement-academic-period"
                            label="Periode akademik"
                            value={form.data.academic_period_id ?? 'none'}
                            error={errors.academic_period_id}
                            onChange={(value) =>
                                form.setData(
                                    'academic_period_id',
                                    value === 'none' ? null : value,
                                )
                            }
                        >
                            <SelectItem value="none">Tidak dipilih</SelectItem>
                            {options.academicPeriods.map((period) => (
                                <SelectItem
                                    key={period.value}
                                    value={period.value}
                                >
                                    {period.label}
                                </SelectItem>
                            ))}
                        </PrestasiSelectField>
                        <PrestasiSelectField
                            id="student-achievement-mentor"
                            label="Pembimbing"
                            value={form.data.mentor_employee_id ?? 'none'}
                            error={errors.mentor_employee_id}
                            onChange={(value) =>
                                form.setData(
                                    'mentor_employee_id',
                                    value === 'none' ? null : value,
                                )
                            }
                        >
                            <SelectItem value="none">Belum ditetapkan</SelectItem>
                            {options.officers.map((officer) => (
                                <SelectItem
                                    key={officer.value}
                                    value={officer.value}
                                >
                                    {officer.label}
                                </SelectItem>
                            ))}
                        </PrestasiSelectField>
                        <PrestasiTextField
                            id="student-achievement-organizer"
                            label="Penyelenggara"
                            value={form.data.organizer ?? ''}
                            error={errors.organizer}
                            placeholder="Opsional"
                            onChange={(value) =>
                                form.setData('organizer', nullable(value))
                            }
                        />
                        <PrestasiTextField
                            id="student-achievement-event"
                            label="Nama kegiatan"
                            value={form.data.event_name ?? ''}
                            error={errors.event_name}
                            placeholder="Opsional"
                            onChange={(value) =>
                                form.setData('event_name', nullable(value))
                            }
                        />
                        <PrestasiTextField
                            id="student-achievement-location"
                            label="Lokasi kegiatan"
                            value={form.data.event_location ?? ''}
                            error={errors.event_location}
                            placeholder="Opsional"
                            onChange={(value) =>
                                form.setData('event_location', nullable(value))
                            }
                        />
                        <PrestasiTextField
                            id="student-achievement-period-start"
                            label="Awal periode"
                            type="date"
                            value={form.data.period_started_on}
                            error={errors.period_started_on}
                            onChange={(value) =>
                                form.setData('period_started_on', value)
                            }
                        />
                        <PrestasiTextField
                            id="student-achievement-period-end"
                            label="Akhir periode"
                            type="date"
                            value={form.data.period_ended_on}
                            error={errors.period_ended_on}
                            onChange={(value) =>
                                form.setData('period_ended_on', value)
                            }
                        />
                    </div>
                    <PrestasiTextareaField
                        id="student-achievement-description"
                        label="Deskripsi"
                        value={form.data.description ?? ''}
                        error={errors.description}
                        placeholder="Ceritakan konteks prestasi secara singkat"
                        onChange={(value) =>
                            form.setData('description', nullable(value))
                        }
                    />
                    <PrestasiTextareaField
                        id="student-achievement-notes"
                        label="Catatan internal"
                        value={form.data.notes ?? ''}
                        error={errors.notes}
                        placeholder="Opsional, hanya untuk catatan operator"
                        onChange={(value) =>
                            form.setData('notes', nullable(value))
                        }
                    />
                    {isEdit ? (
                        <PrestasiTextareaField
                            id="student-achievement-revision-reason"
                            label="Alasan perubahan"
                            value={form.data.revision_reason ?? ''}
                            error={errors.revision_reason}
                            placeholder="Contoh: memperbaiki nama kegiatan"
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
                        <LoadingButton loading={form.processing}>
                            {isEdit
                                ? 'Simpan perubahan'
                                : 'Buat draft prestasi'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function achievementDefaults(
    achievement: StudentAchievement | null,
    options: StudentAchievementIndexPageProps['options'],
): StudentAchievementMutationPayload {
    return {
        student_id: achievement?.student_id ?? options.students[0]?.value ?? 'none',
        category_id:
            achievement?.category.id ?? options.categories[0]?.value ?? 'none',
        academic_period_id:
            achievement?.academic_period_id ??
            options.academicPeriods[0]?.value ??
            null,
        mentor_employee_id: achievement?.mentor_employee_id ?? null,
        title: achievement?.title ?? '',
        achievement_type: achievement?.achievement_type ?? 'competition',
        level: achievement?.level ?? 'internal',
        result: achievement?.result ?? '',
        organizer: achievement?.organizer ?? null,
        event_name: achievement?.event_name ?? null,
        event_location: achievement?.event_location ?? null,
        achieved_on: achievement?.achieved_on ?? '',
        period_started_on: achievement?.period_started_on ?? '',
        period_ended_on: achievement?.period_ended_on ?? '',
        description: achievement?.description ?? null,
        notes: achievement?.notes ?? null,
        revision_reason: '',
    };
}
