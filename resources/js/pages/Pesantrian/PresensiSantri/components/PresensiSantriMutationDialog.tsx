import { useForm } from '@inertiajs/react';
import { ClipboardPlus, PencilLine, Plus, Trash2 } from 'lucide-react';
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
    StudentAttendance,
    StudentAttendanceEntryPayload,
    StudentAttendanceEntryStatus,
    StudentAttendanceIndexPageProps,
    StudentAttendanceSessionPayload,
    StudentAttendanceContext,
} from '../types';
import { entryStatusLabels } from './presensiSantriDisplay';

type Props = {
    open: boolean;
    attendance: StudentAttendance | null;
    options: StudentAttendanceIndexPageProps['options'];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey =
    | keyof StudentAttendanceSessionPayload
    | `entries.${number}.student_id`
    | `entries.${number}.status`
    | `entries.${number}.minutes_late`
    | `entries.${number}.note`
    | 'payload';

const entryStatusOptions = Object.entries(entryStatusLabels) as [
    StudentAttendanceEntryStatus,
    string,
][];

export function PresensiSantriMutationDialog({
    open,
    attendance,
    options,
    onOpenChange,
}: Props) {
    const isEdit = attendance !== null;
    const form = useForm<StudentAttendanceSessionPayload>(
        attendanceDefaults(attendance),
    );
    const errors = form.errors as Partial<Record<ErrorKey, string>>;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const url =
            attendance === null
                ? routeOr(
                      '/pesantrian/student-attendances',
                      'pesantrian.student-attendances.store',
                  )
                : routeOr(
                      `/pesantrian/student-attendances/${attendance.id}`,
                      'pesantrian.student-attendances.update',
                      attendance.id,
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

        if (attendance === null) {
            form.post(url, submitOptions);

            return;
        }

        form.patch(url, submitOptions);
    };

    const addEntry = () =>
        form.setData('entries', [
            ...form.data.entries,
            emptyEntry(options.students[0]?.id ?? ''),
        ]);

    const removeEntry = (index: number) =>
        form.setData(
            'entries',
            form.data.entries.filter((_, current) => current !== index),
        );

    const updateEntry = (
        index: number,
        changes: Partial<StudentAttendanceEntryPayload>,
    ) =>
        form.setData(
            'entries',
            form.data.entries.map((entry, current) =>
                current === index ? { ...entry, ...changes } : entry,
            ),
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
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
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
                                    ? 'Edit sesi presensi'
                                    : 'Tambah sesi presensi'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Isi tanggal, konteks, kode, nama sesi, dan
                                entry awal jika daftar santri sudah siap.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {errors.payload ? <FieldError message={errors.payload} /> : null}

                <form onSubmit={submit} className="space-y-5">
                    <section className="grid gap-4 sm:grid-cols-2">
                        <TextField
                            id="attendance-date"
                            label="Tanggal presensi"
                            type="date"
                            value={form.data.attendance_date}
                            error={fieldError(errors, 'attendance_date')}
                            required
                            onChange={(value) =>
                                form.setData('attendance_date', value)
                            }
                        />
                        <SelectField
                            id="attendance-context-type"
                            label="Konteks presensi"
                            value={form.data.context_type}
                            error={fieldError(errors, 'context_type')}
                            options={options.contexts.map((context) => [
                                context.value,
                                context.label,
                            ])}
                            onChange={(value) =>
                                form.setData(
                                    'context_type',
                                    value as StudentAttendanceContext,
                                )
                            }
                        />
                        <TextField
                            id="attendance-context-name"
                            label="Nama konteks"
                            value={form.data.context_name}
                            error={fieldError(errors, 'context_name')}
                            placeholder="Contoh: VII A, Asrama Putra, Muhadharah"
                            required
                            onChange={(value) =>
                                form.setData('context_name', value)
                            }
                        />
                        <TextField
                            id="attendance-session-code"
                            label="Kode sesi"
                            value={form.data.session_code}
                            error={fieldError(errors, 'session_code')}
                            placeholder="Contoh: KBM-PAGI"
                            required
                            onChange={(value) =>
                                form.setData('session_code', value)
                            }
                        />
                        <TextField
                            id="attendance-session-name"
                            label="Nama sesi"
                            value={form.data.session_name}
                            error={fieldError(errors, 'session_name')}
                            placeholder="Contoh: KBM Pagi"
                            required
                            onChange={(value) =>
                                form.setData('session_name', value)
                            }
                        />
                    </section>

                    {!isEdit ? (
                        <section className="space-y-3 rounded-xl border p-4">
                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 className="text-sm font-semibold">
                                        Entry awal presensi
                                    </h3>
                                    <p className="text-xs text-foreground/60">
                                        Opsional. Bisa dikosongkan dulu dan
                                        dilengkapi dari halaman detail.
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={addEntry}
                                >
                                    <Plus
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    Tambah entry
                                </Button>
                            </div>

                            <div className="space-y-3">
                                {form.data.entries.map((entry, index) => (
                                    <EntryRow
                                        key={index}
                                        entry={entry}
                                        index={index}
                                        students={options.students}
                                        errors={errors}
                                        onChange={(changes) =>
                                            updateEntry(index, changes)
                                        }
                                        onRemove={() => removeEntry(index)}
                                    />
                                ))}
                                {form.data.entries.length === 0 ? (
                                    <p className="rounded-lg border border-dashed p-3 text-sm text-foreground/60">
                                        Belum ada entry awal.
                                    </p>
                                ) : null}
                            </div>
                        </section>
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
                            {isEdit ? 'Simpan sesi' : 'Buat sesi'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function EntryRow({
    entry,
    index,
    students,
    errors,
    onChange,
    onRemove,
}: {
    entry: StudentAttendanceEntryPayload;
    index: number;
    students: StudentAttendanceIndexPageProps['options']['students'];
    errors: Partial<Record<ErrorKey, string>>;
    onChange: (changes: Partial<StudentAttendanceEntryPayload>) => void;
    onRemove: () => void;
}) {
    return (
        <div className="grid gap-3 rounded-lg border p-3 md:grid-cols-[1fr_150px_120px_1fr_auto]">
            <SelectField
                id={`attendance-entry-student-${index}`}
                label="Santri"
                value={entry.student_id}
                error={fieldError(errors, `entries.${index}.student_id`)}
                options={students.map((student) => [
                    student.id,
                    `${student.name} (${student.code})`,
                ])}
                onChange={(value) => onChange({ student_id: value })}
            />
            <SelectField
                id={`attendance-entry-status-${index}`}
                label="Status"
                value={entry.status}
                error={fieldError(errors, `entries.${index}.status`)}
                options={entryStatusOptions}
                onChange={(value) =>
                    onChange({
                        status: value as StudentAttendanceEntryStatus,
                        minutes_late:
                            value === 'late' ? entry.minutes_late : null,
                    })
                }
            />
            <TextField
                id={`attendance-entry-late-${index}`}
                label="Menit telat"
                type="number"
                value={entry.minutes_late ?? ''}
                error={fieldError(errors, `entries.${index}.minutes_late`)}
                disabled={entry.status !== 'late'}
                onChange={(value) =>
                    onChange({ minutes_late: nullable(value) })
                }
            />
            <TextField
                id={`attendance-entry-note-${index}`}
                label="Catatan"
                value={entry.note ?? ''}
                error={fieldError(errors, `entries.${index}.note`)}
                placeholder="Opsional"
                onChange={(value) => onChange({ note: nullable(value) })}
            />
            <div className="flex items-end">
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    aria-label="Hapus entry"
                    onClick={onRemove}
                >
                    <Trash2 className="size-4" aria-hidden="true" />
                </Button>
            </div>
        </div>
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
    disabled = false,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    type?: string;
    required?: boolean;
    disabled?: boolean;
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
                disabled={disabled}
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

function attendanceDefaults(
    attendance: StudentAttendance | null,
): StudentAttendanceSessionPayload {
    return {
        attendance_date:
            attendance?.attendance_date ?? new Date().toISOString().slice(0, 10),
        context_type: attendance?.context_type ?? 'activity',
        context_id: attendance?.context_id ?? null,
        context_name: attendance?.context_name ?? '',
        session_code: attendance?.session_code ?? '',
        session_name: attendance?.session_name ?? '',
        entries: [],
    };
}

function emptyEntry(studentId: string): StudentAttendanceEntryPayload {
    return {
        student_id: studentId,
        status: 'present',
        minutes_late: null,
        note: null,
        source_reference_type: null,
        source_reference_id: null,
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
