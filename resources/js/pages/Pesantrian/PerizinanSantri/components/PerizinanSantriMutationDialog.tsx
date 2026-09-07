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
    StudentPermit,
    StudentPermitIndexPageProps,
    StudentPermitMutationPayload,
    StudentPermitType,
} from '../types';
import {
    fromDateTimeLocal,
    nullable,
    PerizinanFieldError,
    PerizinanSelectField,
    PerizinanTextareaField,
    PerizinanTextField,
    toDateTimeLocal,
} from './PerizinanSantriFormFields';

type Props = {
    open: boolean;
    permit: StudentPermit | null;
    options: StudentPermitIndexPageProps['options'];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey = keyof StudentPermitMutationPayload | 'student_id' | 'status';

export function PerizinanSantriMutationDialog({
    open,
    permit,
    options,
    onOpenChange,
}: Props) {
    const form = useForm<StudentPermitMutationPayload>(
        permitDefaults(permit, options),
    );
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const isEdit = permit !== null;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const payload = {
            ...form.data,
            student_id:
                form.data.student_id === 'none' ? '' : form.data.student_id,
            destination: nullable(form.data.destination ?? ''),
            starts_at: fromDateTimeLocal(form.data.starts_at),
            ends_at: fromDateTimeLocal(form.data.ends_at),
            revision_reason: isEdit
                ? form.data.revision_reason || 'Koreksi data izin dari UI.'
                : undefined,
        };
        const url =
            permit === null
                ? routeOr(
                      '/pesantrian/student-permits',
                      'pesantrian.student-permits.store',
                  )
                : routeOr(
                      `/pesantrian/student-permits/${permit.id}`,
                      'pesantrian.student-permits.update',
                      permit.id,
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

        if (permit === null) {
            form.transform(() => payload);
            form.post(url, submitOptions);

            return;
        }

        form.transform(() => payload);
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
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
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
                                {isEdit ? 'Edit izin santri' : 'Buat izin santri'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Simpan sebagai draft dulu. Submit dan keputusan
                                dilakukan lewat aksi lifecycle.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <PerizinanFieldError message={errors.status} />
                    ) : null}
                    <div className="grid gap-4 sm:grid-cols-2">
                        <PerizinanSelectField
                            id="student-permit-student"
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
                        </PerizinanSelectField>
                        <PerizinanSelectField
                            id="student-permit-type"
                            label="Jenis izin"
                            value={form.data.permit_type}
                            error={errors.permit_type}
                            onChange={(value) =>
                                form.setData(
                                    'permit_type',
                                    value as StudentPermitType,
                                )
                            }
                        >
                            {options.permitTypes.map((type) => (
                                <SelectItem key={type.value} value={type.value}>
                                    {type.label}
                                </SelectItem>
                            ))}
                        </PerizinanSelectField>
                        <PerizinanTextField
                            id="student-permit-starts-at"
                            label="Mulai izin"
                            type="datetime-local"
                            value={form.data.starts_at}
                            error={errors.starts_at}
                            required
                            onChange={(value) =>
                                form.setData('starts_at', value)
                            }
                        />
                        <PerizinanTextField
                            id="student-permit-ends-at"
                            label="Batas kembali"
                            type="datetime-local"
                            value={form.data.ends_at}
                            error={errors.ends_at}
                            required
                            onChange={(value) => form.setData('ends_at', value)}
                        />
                        <PerizinanTextField
                            id="student-permit-destination"
                            label="Tujuan"
                            value={form.data.destination ?? ''}
                            error={errors.destination}
                            placeholder="Rumah wali, klinik, atau lokasi kegiatan"
                            onChange={(value) =>
                                form.setData('destination', nullable(value))
                            }
                        />
                    </div>
                    <PerizinanTextareaField
                        id="student-permit-reason"
                        label="Alasan izin"
                        value={form.data.reason}
                        error={errors.reason}
                        placeholder="Jelaskan alasan izin dengan singkat dan jelas"
                        required
                        onChange={(value) => form.setData('reason', value)}
                    />
                    {isEdit ? (
                        <PerizinanTextareaField
                            id="student-permit-revision-reason"
                            label="Alasan koreksi"
                            value={form.data.revision_reason ?? ''}
                            error={errors.revision_reason}
                            placeholder="Contoh: koreksi tujuan dan batas kembali"
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
                            {isEdit ? 'Simpan perubahan' : 'Buat draft izin'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function permitDefaults(
    permit: StudentPermit | null,
    options: StudentPermitIndexPageProps['options'],
): StudentPermitMutationPayload {
    return {
        student_id: permit?.student_id ?? options.students[0]?.value ?? 'none',
        permit_type: permit?.permit_type ?? 'home_visit',
        starts_at: toDateTimeLocal(permit?.starts_at),
        ends_at: toDateTimeLocal(permit?.ends_at),
        destination: permit?.destination ?? null,
        reason: permit?.reason ?? '',
        revision_reason: '',
    };
}
