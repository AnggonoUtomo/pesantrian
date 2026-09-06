import { useForm } from '@inertiajs/react';
import { ClipboardEdit, Plus, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
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
    StudentAttendanceEntriesPayload,
    StudentAttendanceEntryPayload,
    StudentAttendanceEntryStatus,
    StudentAttendanceShowPageProps,
} from '../types';
import { entryStatusLabels } from './presensiSantriDisplay';

type Props = {
    attendance: StudentAttendance;
    students: StudentAttendanceShowPageProps['options']['students'];
    canManage: boolean;
};

type ErrorKey =
    | `entries.${number}.student_id`
    | `entries.${number}.status`
    | `entries.${number}.minutes_late`
    | `entries.${number}.note`
    | 'entries'
    | 'payload';

const entryStatusOptions = Object.entries(entryStatusLabels) as [
    StudentAttendanceEntryStatus,
    string,
][];

export function PresensiSantriEntryEditor({
    attendance,
    students,
    canManage,
}: Props) {
    const form = useForm<StudentAttendanceEntriesPayload>({
        entries:
            attendance.entries?.map((entry) => ({
                student_id: entry.student_id,
                status: entry.status,
                minutes_late:
                    entry.minutes_late === null
                        ? null
                        : String(entry.minutes_late),
                note: entry.note,
                source_reference_type: entry.source_reference_type,
                source_reference_id: entry.source_reference_id,
            })) ?? [],
    });
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const editable = canManage && ['draft', 'revised'].includes(attendance.status);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.patch(
            routeOr(
                `/pesantrian/student-attendances/${attendance.id}/entries`,
                'pesantrian.student-attendances.entries.update',
                attendance.id,
            ),
            { preserveScroll: true },
        );
    };

    const addEntry = () =>
        form.setData('entries', [
            ...form.data.entries,
            emptyEntry(students[0]?.id ?? ''),
        ]);

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

    const removeEntry = (index: number) =>
        form.setData(
            'entries',
            form.data.entries.filter((_, current) => current !== index),
        );

    return (
        <section className="dashboard-card dashboard-card--teal rounded-2xl border p-5">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 className="flex items-center gap-2 font-semibold">
                        <ClipboardEdit
                            className="size-4 text-teal-600"
                            aria-hidden="true"
                        />
                        Editor entry presensi
                    </h2>
                    <p className="mt-1 text-sm text-foreground/65">
                        Kelola status hadir, terlambat, izin, sakit, atau alfa.
                        Entry hanya bisa diedit saat sesi draft atau revisi.
                    </p>
                </div>
                {editable ? (
                    <Button type="button" variant="outline" onClick={addEntry}>
                        <Plus className="size-4" aria-hidden="true" />
                        Tambah entry
                    </Button>
                ) : null}
            </div>

            {errors.entries || errors.payload ? (
                <FieldError message={errors.entries ?? errors.payload ?? ''} />
            ) : null}

            <form className="mt-4 space-y-3" onSubmit={submit}>
                {form.data.entries.map((entry, index) => (
                    <div
                        key={index}
                        className="grid gap-3 rounded-xl border bg-background p-3 md:grid-cols-[1fr_150px_120px_1fr_auto]"
                    >
                        <SelectField
                            id={`entry-editor-student-${index}`}
                            label="Santri"
                            value={entry.student_id}
                            disabled={!editable}
                            error={errors[`entries.${index}.student_id`]}
                            options={students.map((student) => [
                                student.id,
                                `${student.name} (${student.code})`,
                            ])}
                            onChange={(value) =>
                                updateEntry(index, { student_id: value })
                            }
                        />
                        <SelectField
                            id={`entry-editor-status-${index}`}
                            label="Status"
                            value={entry.status}
                            disabled={!editable}
                            error={errors[`entries.${index}.status`]}
                            options={entryStatusOptions}
                            onChange={(value) =>
                                updateEntry(index, {
                                    status: value as StudentAttendanceEntryStatus,
                                    minutes_late:
                                        value === 'late'
                                            ? entry.minutes_late
                                            : null,
                                })
                            }
                        />
                        <TextField
                            id={`entry-editor-late-${index}`}
                            label="Menit telat"
                            type="number"
                            value={entry.minutes_late ?? ''}
                            disabled={!editable || entry.status !== 'late'}
                            error={errors[`entries.${index}.minutes_late`]}
                            onChange={(value) =>
                                updateEntry(index, {
                                    minutes_late: nullable(value),
                                })
                            }
                        />
                        <TextField
                            id={`entry-editor-note-${index}`}
                            label="Catatan"
                            value={entry.note ?? ''}
                            disabled={!editable}
                            error={errors[`entries.${index}.note`]}
                            placeholder="Opsional"
                            onChange={(value) =>
                                updateEntry(index, { note: nullable(value) })
                            }
                        />
                        <div className="flex items-end">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                aria-label="Hapus entry presensi"
                                disabled={!editable}
                                onClick={() => removeEntry(index)}
                            >
                                <Trash2 className="size-4" aria-hidden="true" />
                            </Button>
                        </div>
                    </div>
                ))}

                {form.data.entries.length === 0 ? (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/60">
                        Belum ada entry. Tambahkan santri aktif untuk mulai
                        mengisi presensi.
                    </p>
                ) : null}

                {editable ? (
                    <div className="flex justify-end">
                        <LoadingButton type="submit" loading={form.processing}>
                            Simpan entry presensi
                        </LoadingButton>
                    </div>
                ) : null}
            </form>
        </section>
    );
}

function TextField({
    id,
    label,
    value,
    error,
    placeholder,
    type = 'text',
    disabled = false,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    type?: string;
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
    disabled = false,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    options: [string, string][];
    disabled?: boolean;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Select value={value} disabled={disabled} onValueChange={onChange}>
                <SelectTrigger id={id} aria-invalid={error ? true : undefined}>
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
