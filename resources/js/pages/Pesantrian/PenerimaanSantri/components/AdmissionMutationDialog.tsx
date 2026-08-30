import { router } from '@inertiajs/react';
import { NotebookPen, PencilLine } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
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
    AdmissionChecklistStatus,
    AdmissionMutationPayload,
    AdmissionTargetUnitOption,
    CandidateGender,
    RegistrationFeeStatus,
    StudentAdmission,
} from '../types';

type FieldErrors = Record<string, string>;

type Props = {
    open: boolean;
    admission: StudentAdmission | null;
    targetUnitOptions: AdmissionTargetUnitOption[];
    onOpenChange: (open: boolean) => void;
};

const checklistLabels: Record<string, string> = {
    akta_kelahiran: 'Akta kelahiran',
    kartu_keluarga: 'Kartu keluarga',
    ijazah_terakhir: 'Ijazah terakhir',
};

const checklistStatusLabels: Record<AdmissionChecklistStatus, string> = {
    not_submitted: 'Belum diserahkan',
    submitted: 'Diserahkan',
    verified: 'Terverifikasi',
    rejected: 'Perlu revisi',
};

export function AdmissionMutationDialog({
    open,
    admission,
    targetUnitOptions,
    onOpenChange,
}: Props) {
    const isEdit = admission !== null;
    const [form, setForm] = useState<AdmissionMutationPayload>(() =>
        admissionDefaults(admission),
    );
    const [errors, setErrors] = useState<FieldErrors>({});
    const [processing, setProcessing] = useState(false);

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setProcessing(true);
        setErrors({});

        const response = await submitAdmission(form, admission);

        setProcessing(false);

        if (!response.ok) {
            setErrors(response.errors);
            toast.error(response.message);

            return;
        }

        toast.success(
            isEdit
                ? 'Pendaftaran santri berhasil diperbarui.'
                : 'Pendaftaran santri berhasil dibuat.',
        );
        onOpenChange(false);
        router.reload({ only: ['admissions'] });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => !processing && onOpenChange(nextOpen)}
        >
            <DialogContent className="max-h-[90vh] max-w-3xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine className="size-5" />
                            ) : (
                                <NotebookPen className="size-5" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit pendaftaran'
                                    : 'Tambah pendaftaran'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Isi data calon santri, wali, administrasi biaya,
                                dan checklist dokumen awal PPDB.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {errors.payload ? (
                    <p role="alert" className="dashboard-message--error text-sm">
                        {errors.payload}
                    </p>
                ) : null}

                <form onSubmit={submit} className="space-y-5">
                    <section className="grid gap-4 sm:grid-cols-2">
                        <TextField
                            id="admission-period"
                            label="Periode PPDB"
                            value={form.registration_period ?? ''}
                            error={errors.registration_period}
                            placeholder="PPDB 2027"
                            onChange={(value) =>
                                setFormValue(
                                    setForm,
                                    'registration_period',
                                    nullable(value),
                                )
                            }
                        />
                        <SelectField
                            id="admission-status"
                            label="Status awal"
                            value={form.status}
                            error={errors.status}
                            options={[
                                ['draft', 'Draft'],
                                ['submitted', 'Diajukan'],
                                ['verified', 'Terverifikasi'],
                            ]}
                            onChange={(value) =>
                                setFormValue(
                                    setForm,
                                    'status',
                                    value as AdmissionMutationPayload['status'],
                                )
                            }
                        />
                    </section>

                    <section className="space-y-3">
                        <h3 className="text-sm font-semibold">
                            Data calon santri
                        </h3>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <TextField
                                id="candidate-name"
                                label="Nama calon santri"
                                value={form.candidate_name}
                                error={errors.candidate_name}
                                placeholder="Aisyah Rahma"
                                required
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'candidate_name',
                                        value,
                                    )
                                }
                            />
                            <SelectField
                                id="candidate-gender"
                                label="Jenis kelamin"
                                value={form.candidate_gender ?? 'none'}
                                error={errors.candidate_gender}
                                options={[
                                    ['none', 'Belum diisi'],
                                    ['male', 'Laki-laki'],
                                    ['female', 'Perempuan'],
                                ]}
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'candidate_gender',
                                        value === 'none'
                                            ? null
                                            : (value as CandidateGender),
                                    )
                                }
                            />
                            <TextField
                                id="candidate-birth-place"
                                label="Tempat lahir"
                                value={form.candidate_birth_place ?? ''}
                                error={errors.candidate_birth_place}
                                placeholder="Bandung"
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'candidate_birth_place',
                                        nullable(value),
                                    )
                                }
                            />
                            <TextField
                                id="candidate-birth-date"
                                label="Tanggal lahir"
                                type="date"
                                value={form.candidate_birth_date ?? ''}
                                error={errors.candidate_birth_date}
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'candidate_birth_date',
                                        nullable(value),
                                    )
                                }
                            />
                            <TextField
                                id="previous-school"
                                label="Sekolah asal"
                                value={form.previous_school ?? ''}
                                error={errors.previous_school}
                                placeholder="SD Negeri 1"
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'previous_school',
                                        nullable(value),
                                    )
                                }
                            />
                            <SelectField
                                id="target-unit"
                                label="Unit tujuan"
                                value={form.target_unit_id ?? 'none'}
                                error={errors.target_unit_id}
                                options={[
                                    ['none', 'Belum dipilih'],
                                    ...targetUnitOptions.map((unit) => [
                                        unit.id,
                                        `${unit.name} (${unit.code})`,
                                    ] as [string, string]),
                                ]}
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'target_unit_id',
                                        value === 'none' ? null : value,
                                    )
                                }
                            />
                        </div>
                    </section>

                    <section className="space-y-3">
                        <h3 className="text-sm font-semibold">Data wali</h3>
                        <div className="grid gap-4 sm:grid-cols-3">
                            <TextField
                                id="guardian-name"
                                label="Nama wali"
                                value={form.guardian_name}
                                error={errors.guardian_name}
                                placeholder="Siti Aminah"
                                required
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'guardian_name',
                                        value,
                                    )
                                }
                            />
                            <TextField
                                id="guardian-phone"
                                label="Nomor HP wali"
                                value={form.guardian_phone ?? ''}
                                error={errors.guardian_phone}
                                placeholder="081234567890"
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'guardian_phone',
                                        nullable(value),
                                    )
                                }
                            />
                            <SelectField
                                id="guardian-relation"
                                label="Hubungan wali"
                                value={form.guardian_relation ?? 'none'}
                                error={errors.guardian_relation}
                                options={[
                                    ['none', 'Belum diisi'],
                                    ['ayah', 'Ayah'],
                                    ['ibu', 'Ibu'],
                                    ['wali', 'Wali'],
                                ]}
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'guardian_relation',
                                        value === 'none' ? null : value,
                                    )
                                }
                            />
                        </div>
                    </section>

                    <section className="space-y-3">
                        <h3 className="text-sm font-semibold">
                            Administrasi biaya
                        </h3>
                        <div className="grid gap-4 sm:grid-cols-[180px_1fr_1fr]">
                            <div className="space-y-2">
                                <Label
                                    htmlFor="registration-fee-required"
                                    className="block"
                                >
                                    Wajib bayar
                                </Label>
                                <label className="flex h-9 items-center gap-2 rounded-md border px-3 text-sm">
                                    <Checkbox
                                        id="registration-fee-required"
                                        checked={
                                            form.registration_fee_required
                                        }
                                        onCheckedChange={(checked) =>
                                            setFormValue(
                                                setForm,
                                                'registration_fee_required',
                                                checked === true,
                                            )
                                        }
                                    />
                                    Ya
                                </label>
                            </div>
                            <TextField
                                id="registration-fee-amount"
                                label="Nominal"
                                type="number"
                                value={form.registration_fee_amount ?? ''}
                                error={errors.registration_fee_amount}
                                placeholder="250000"
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'registration_fee_amount',
                                        nullable(value),
                                    )
                                }
                            />
                            <SelectField
                                id="registration-fee-status"
                                label="Status biaya"
                                value={form.registration_fee_status}
                                error={errors.registration_fee_status}
                                options={[
                                    ['not_required', 'Tidak wajib'],
                                    ['pending', 'Menunggu'],
                                    ['verified', 'Terverifikasi'],
                                    ['rejected', 'Ditolak'],
                                ]}
                                onChange={(value) =>
                                    setFormValue(
                                        setForm,
                                        'registration_fee_status',
                                        value as RegistrationFeeStatus,
                                    )
                                }
                            />
                        </div>
                    </section>

                    <section className="space-y-3">
                        <h3 className="text-sm font-semibold">
                            Checklist dokumen
                        </h3>
                        <div className="grid gap-3">
                            {form.document_checklist.map((item, index) => (
                                <div
                                    key={item.type}
                                    className="grid gap-3 rounded-xl border p-3 sm:grid-cols-[1fr_180px_1.5fr]"
                                >
                                    <div>
                                        <div className="text-sm font-medium">
                                            {checklistLabels[item.type] ??
                                                item.type}
                                        </div>
                                        <div className="text-xs text-foreground/60">
                                            {item.type}
                                        </div>
                                    </div>
                                    <SelectField
                                        id={`document-status-${item.type}`}
                                        label="Status"
                                        value={item.status}
                                        error={
                                            errors[
                                                `document_checklist.${index}.status`
                                            ]
                                        }
                                        options={Object.entries(
                                            checklistStatusLabels,
                                        )}
                                        onChange={(value) =>
                                            setChecklistValue(
                                                setForm,
                                                index,
                                                'status',
                                                value as AdmissionChecklistStatus,
                                            )
                                        }
                                    />
                                    <TextField
                                        id={`document-notes-${item.type}`}
                                        label="Catatan"
                                        value={item.notes}
                                        error={
                                            errors[
                                                `document_checklist.${index}.notes`
                                            ]
                                        }
                                        placeholder="Opsional"
                                        onChange={(value) =>
                                            setChecklistValue(
                                                setForm,
                                                index,
                                                'notes',
                                                value,
                                            )
                                        }
                                    />
                                </div>
                            ))}
                        </div>
                    </section>

                    <div className="space-y-2">
                        <Label htmlFor="admission-notes">Catatan internal</Label>
                        <textarea
                            id="admission-notes"
                            className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive min-h-24 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                            value={form.notes ?? ''}
                            placeholder="Catatan opsional untuk operator."
                            maxLength={1000}
                            onChange={(event) =>
                                setFormValue(
                                    setForm,
                                    'notes',
                                    nullable(event.target.value),
                                )
                            }
                        />
                        {errors.notes ? (
                            <FieldError message={errors.notes} />
                        ) : null}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={processing}>
                            {processing
                                ? 'Menyimpan...'
                                : isEdit
                                  ? 'Simpan perubahan'
                                  : 'Tambah pendaftaran'}
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

function admissionDefaults(
    admission: StudentAdmission | null,
): AdmissionMutationPayload {
    return {
        registration_period: admission?.registration_period ?? null,
        candidate_name: admission?.candidate_name ?? '',
        candidate_gender: admission?.candidate_gender ?? null,
        candidate_birth_place: admission?.candidate_birth_place ?? null,
        candidate_birth_date: admission?.candidate_birth_date ?? null,
        previous_school: admission?.previous_school ?? null,
        target_unit_id: admission?.target_unit_id ?? null,
        guardian_name: admission?.guardian_name ?? '',
        guardian_phone: admission?.guardian_phone ?? null,
        guardian_relation: admission?.guardian_relation ?? null,
        registration_fee_required:
            admission?.registration_fee_required ?? false,
        registration_fee_amount: admission?.registration_fee_amount ?? null,
        registration_fee_status:
            admission?.registration_fee_status ?? 'not_required',
        document_checklist:
            admission?.document_checklist &&
            admission.document_checklist.length > 0
                ? admission.document_checklist
                : defaultChecklist(),
        status:
            admission?.status && ['draft', 'submitted', 'verified'].includes(admission.status)
                ? (admission.status as AdmissionMutationPayload['status'])
                : 'draft',
        notes: admission?.notes ?? null,
    };
}

function defaultChecklist() {
    return [
        { type: 'akta_kelahiran', status: 'not_submitted', notes: '' },
        { type: 'kartu_keluarga', status: 'not_submitted', notes: '' },
        { type: 'ijazah_terakhir', status: 'not_submitted', notes: '' },
    ] satisfies AdmissionMutationPayload['document_checklist'];
}

function nullable(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}

function setFormValue<K extends keyof AdmissionMutationPayload>(
    setForm: React.Dispatch<React.SetStateAction<AdmissionMutationPayload>>,
    key: K,
    value: AdmissionMutationPayload[K],
) {
    setForm((current) => ({ ...current, [key]: value }));
}

function setChecklistValue<K extends keyof AdmissionMutationPayload['document_checklist'][number]>(
    setForm: React.Dispatch<React.SetStateAction<AdmissionMutationPayload>>,
    index: number,
    key: K,
    value: AdmissionMutationPayload['document_checklist'][number][K],
) {
    setForm((current) => ({
        ...current,
        document_checklist: current.document_checklist.map((item, itemIndex) =>
            itemIndex === index ? { ...item, [key]: value } : item,
        ),
    }));
}

async function submitAdmission(
    payload: AdmissionMutationPayload,
    admission: StudentAdmission | null,
): Promise<{ ok: true } | { ok: false; message: string; errors: FieldErrors }> {
    const url =
        admission === null
            ? routeOr(
                  '/api/v1/pesantrian/admissions',
                  'api.v1.pesantrian.admissions.store',
              )
            : routeOr(
                  `/api/v1/pesantrian/admissions/${admission.id}`,
                  'api.v1.pesantrian.admissions.update',
                  admission.id,
              );
    const response = await fetch(url, {
        method: admission === null ? 'POST' : 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'Idempotency-Key': idempotencyKey(),
            'X-Requested-With': 'XMLHttpRequest',
            ...csrfHeaders(),
        },
        body: JSON.stringify(payload),
    });
    const data = (await response.json().catch(() => null)) as
        | { message?: string; errors?: Record<string, string[]> }
        | null;

    if (response.ok) {
        return { ok: true };
    }

    return {
        ok: false,
        message: data?.message ?? 'Pendaftaran santri tidak dapat disimpan.',
        errors: flattenErrors(data?.errors),
    };
}

function csrfHeaders(): Record<string, string> {
    const token = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
        ?.split('=')
        .slice(1)
        .join('=');

    return token ? { 'X-XSRF-TOKEN': decodeURIComponent(token) } : {};
}

function idempotencyKey(): string {
    if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
        return crypto.randomUUID();
    }

    return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function flattenErrors(
    errors: Record<string, string[]> | undefined,
): FieldErrors {
    if (!errors) {
        return {};
    }

    return Object.fromEntries(
        Object.entries(errors).map(([field, messages]) => [
            field,
            messages[0] ?? 'Input tidak valid.',
        ]),
    );
}
