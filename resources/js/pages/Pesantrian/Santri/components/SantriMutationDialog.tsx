import { useForm } from '@inertiajs/react';
import { NotebookPen, PencilLine } from 'lucide-react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoadingButton } from '@/components/ui/loading-button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { routeOr } from '@/lib/route';
import type {
    PrimaryUnitOption,
    Student,
    StudentGender,
    StudentMutationPayload,
} from '../types';

type Props = {
    open: boolean;
    student: Student | null;
    primaryUnitOptions: PrimaryUnitOption[];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey = keyof StudentMutationPayload | 'payload';

export function SantriMutationDialog({
    open,
    student,
    primaryUnitOptions,
    onOpenChange,
}: Props) {
    const isEdit = student !== null;
    const form = useForm<StudentMutationPayload>(studentDefaults(student));
    const mutationErrors = form.errors as Partial<Record<ErrorKey, string>>;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const url =
            student === null
                ? routeOr('/pesantrian/students', 'pesantrian.students.store')
                : routeOr(
                      `/pesantrian/students/${student.id}`,
                      'pesantrian.students.update',
                      student.id,
                  );
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                if (!isEdit) {
                    form.reset();
                }

                onOpenChange(false);
            },
        };

        if (student === null) {
            form.post(url, options);

            return;
        }

        form.patch(url, options);
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
                            {isEdit ? (
                                <PencilLine
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            ) : (
                                <NotebookPen
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit data santri'
                                    : 'Tambah santri manual'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Isi data induk dan wali snapshot minimum. NIS
                                dibuat otomatis oleh sistem.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {mutationErrors.payload ? (
                    <FieldError message={mutationErrors.payload} />
                ) : null}

                <form onSubmit={submit} className="space-y-5">
                    <section className="space-y-3">
                        <h3 className="text-sm font-semibold">
                            Data induk santri
                        </h3>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <TextField
                                id="student-full-name"
                                label="Nama lengkap"
                                value={form.data.full_name}
                                error={fieldError(mutationErrors, 'full_name')}
                                placeholder="Aisyah Rahma"
                                required
                                onChange={(value) =>
                                    form.setData('full_name', value)
                                }
                            />
                            <TextField
                                id="student-preferred-name"
                                label="Nama panggilan"
                                value={form.data.preferred_name ?? ''}
                                error={fieldError(
                                    mutationErrors,
                                    'preferred_name',
                                )}
                                placeholder="Aisyah"
                                onChange={(value) =>
                                    form.setData(
                                        'preferred_name',
                                        nullable(value),
                                    )
                                }
                            />
                            <SelectField
                                id="student-gender"
                                label="Jenis kelamin"
                                value={form.data.gender ?? 'none'}
                                error={fieldError(mutationErrors, 'gender')}
                                options={[
                                    ['none', 'Belum diisi'],
                                    ['male', 'Laki-laki'],
                                    ['female', 'Perempuan'],
                                ]}
                                onChange={(value) =>
                                    form.setData(
                                        'gender',
                                        value === 'none'
                                            ? null
                                            : (value as StudentGender),
                                    )
                                }
                            />
                            <TextField
                                id="student-birth-place"
                                label="Tempat lahir"
                                value={form.data.birth_place ?? ''}
                                error={fieldError(mutationErrors, 'birth_place')}
                                placeholder="Bandung"
                                onChange={(value) =>
                                    form.setData(
                                        'birth_place',
                                        nullable(value),
                                    )
                                }
                            />
                            <TextField
                                id="student-birth-date"
                                label="Tanggal lahir"
                                type="date"
                                value={form.data.birth_date ?? ''}
                                error={fieldError(mutationErrors, 'birth_date')}
                                onChange={(value) =>
                                    form.setData(
                                        'birth_date',
                                        nullable(value),
                                    )
                                }
                            />
                            <TextField
                                id="student-previous-school"
                                label="Sekolah asal"
                                value={form.data.previous_school ?? ''}
                                error={fieldError(
                                    mutationErrors,
                                    'previous_school',
                                )}
                                placeholder="SD Negeri 1"
                                onChange={(value) =>
                                    form.setData(
                                        'previous_school',
                                        nullable(value),
                                    )
                                }
                            />
                            <SelectField
                                id="student-primary-unit"
                                label="Unit utama"
                                value={form.data.primary_unit_id ?? 'none'}
                                error={fieldError(
                                    mutationErrors,
                                    'primary_unit_id',
                                )}
                                options={[
                                    ['none', 'Belum dipilih'],
                                    ...primaryUnitOptions.map((unit) => [
                                        unit.id,
                                        `${unit.name} (${unit.code})`,
                                    ] as [string, string]),
                                ]}
                                onChange={(value) =>
                                    form.setData(
                                        'primary_unit_id',
                                        value === 'none' ? null : value,
                                    )
                                }
                            />
                            <TextField
                                id="student-entry-date"
                                label="Tanggal masuk"
                                type="date"
                                value={form.data.entry_date ?? ''}
                                error={fieldError(mutationErrors, 'entry_date')}
                                onChange={(value) =>
                                    form.setData('entry_date', nullable(value))
                                }
                            />
                        </div>
                    </section>

                    <section className="space-y-3">
                        <h3 className="text-sm font-semibold">
                            Wali snapshot
                        </h3>
                        <div className="grid gap-4 sm:grid-cols-3">
                            <TextField
                                id="student-guardian-name"
                                label="Nama wali"
                                value={form.data.guardian_name}
                                error={fieldError(
                                    mutationErrors,
                                    'guardian_name',
                                )}
                                placeholder="Siti Aminah"
                                required
                                onChange={(value) =>
                                    form.setData('guardian_name', value)
                                }
                            />
                            <TextField
                                id="student-guardian-phone"
                                label="Nomor HP wali"
                                value={form.data.guardian_phone ?? ''}
                                error={fieldError(
                                    mutationErrors,
                                    'guardian_phone',
                                )}
                                placeholder="081234567890"
                                onChange={(value) =>
                                    form.setData(
                                        'guardian_phone',
                                        nullable(value),
                                    )
                                }
                            />
                            <SelectField
                                id="student-guardian-relation"
                                label="Hubungan wali"
                                value={form.data.guardian_relation ?? 'none'}
                                error={fieldError(
                                    mutationErrors,
                                    'guardian_relation',
                                )}
                                options={[
                                    ['none', 'Belum diisi'],
                                    ['ayah', 'Ayah'],
                                    ['ibu', 'Ibu'],
                                    ['wali', 'Wali'],
                                ]}
                                onChange={(value) =>
                                    form.setData(
                                        'guardian_relation',
                                        value === 'none'
                                            ? null
                                            : (value as StudentMutationPayload['guardian_relation']),
                                    )
                                }
                            />
                        </div>
                        <label className="flex w-fit items-center gap-2 rounded-md border px-3 py-2 text-sm">
                            <Checkbox
                                checked={form.data.is_emergency_contact}
                                onCheckedChange={(checked) =>
                                    form.setData(
                                        'is_emergency_contact',
                                        checked === true,
                                    )
                                }
                            />
                            Kontak darurat
                        </label>
                        {fieldError(mutationErrors, 'is_emergency_contact') ? (
                            <FieldError
                                message={
                                    fieldError(
                                        mutationErrors,
                                        'is_emergency_contact',
                                    ) ?? ''
                                }
                            />
                        ) : null}
                    </section>

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
                            {isEdit ? 'Simpan perubahan' : 'Tambah santri'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function TextField({
    id,
    label,
    value,
    error,
    placeholder,
    type = 'text',
    required = false,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    type?: string;
    required?: boolean;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Input
                id={id}
                type={type}
                value={value}
                placeholder={placeholder}
                required={required}
                aria-invalid={error ? true : undefined}
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <FieldError message={error} /> : null}
        </div>
    );
}

function SelectField({
    id,
    label,
    value,
    error,
    options,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    options: [string, string][];
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger
                    id={id}
                    className="w-full"
                    aria-invalid={error ? true : undefined}
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {options.map(([optionValue, label]) => (
                        <SelectItem key={optionValue} value={optionValue}>
                            {label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error ? <FieldError message={error} /> : null}
        </div>
    );
}

function FieldError({ message }: { message: string }) {
    return (
        <p className="text-xs text-destructive" role="alert">
            {message}
        </p>
    );
}

function studentDefaults(student: Student | null): StudentMutationPayload {
    return {
        full_name: student?.full_name ?? '',
        preferred_name: student?.preferred_name ?? null,
        gender: student?.gender ?? null,
        birth_place: student?.birth_place ?? null,
        birth_date: student?.birth_date ?? null,
        previous_school: student?.previous_school ?? null,
        primary_unit_id: student?.primary_unit_id ?? null,
        entry_date: student?.entry_date ?? null,
        guardian_name: student?.primary_guardian?.guardian_name ?? '',
        guardian_phone: student?.primary_guardian?.guardian_phone ?? null,
        guardian_relation:
            (student?.primary_guardian
                ?.guardian_relation as StudentMutationPayload['guardian_relation']) ??
            null,
        is_emergency_contact:
            student?.primary_guardian?.is_emergency_contact ?? false,
    };
}

function nullable(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}

function fieldError(
    errors: Partial<Record<ErrorKey, string>>,
    key: ErrorKey,
): string | undefined {
    return errors[key];
}
