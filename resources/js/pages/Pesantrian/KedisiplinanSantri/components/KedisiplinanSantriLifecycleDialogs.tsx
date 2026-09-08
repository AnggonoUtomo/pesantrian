import { useForm } from '@inertiajs/react';
import { Ban, CheckCircle2, ClipboardCheck, Send } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
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
import type { RouteName } from '@/lib/route';
import type {
    StudentDisciplineActionPayload,
    StudentDisciplineCase,
    StudentDisciplineIndexPageProps,
    StudentDisciplineResolvePayload,
    StudentDisciplineReviewPayload,
    StudentDisciplineVoidPayload,
} from '../types';
import { disciplineStatusLabel } from './kedisiplinanSantriDisplay';
import {
    KedisiplinanFieldError,
    KedisiplinanSelectField,
    KedisiplinanTextareaField,
} from './KedisiplinanSantriFormFields';

type DialogProps = {
    open: boolean;
    case: StudentDisciplineCase;
    onOpenChange: (open: boolean) => void;
};

export function SubmitKedisiplinanDialog({
    open,
    case: disciplineCase,
    onOpenChange,
}: DialogProps) {
    return (
        <SimpleLifecycleDialog
            open={open}
            case={disciplineCase}
            title="Submit kasus"
            description="Draft kasus akan diajukan untuk review pembina/petugas."
            confirmLabel="Submit kasus"
            routeName="pesantrian.student-discipline-cases.submit"
            fallback={`/pesantrian/student-discipline-cases/${disciplineCase.id}/submit`}
            icon={Send}
            onOpenChange={onOpenChange}
        />
    );
}

export function ReviewKedisiplinanDialog({
    open,
    case: disciplineCase,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentDisciplineReviewPayload>({ review_note: '' });
    const errors = form.errors as Partial<
        Record<keyof StudentDisciplineReviewPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/student-discipline-cases/${disciplineCase.id}/review`,
                'pesantrian.student-discipline-cases.review',
                disciplineCase.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            },
        );
    };

    return (
        <TextLifecycleDialog
            open={open}
            title="Review kasus"
            description="Review hanya untuk kasus yang sudah disubmit."
            label="Catatan review"
            placeholder="Contoh: kronologi sudah diklarifikasi dengan santri"
            value={form.data.review_note}
            error={errors.review_note ?? errors.status}
            processing={form.processing}
            submitLabel="Simpan review"
            onChange={(value) => form.setData('review_note', value)}
            onSubmit={submit}
            onCancel={() => onOpenChange(false)}
        />
    );
}

export function AssignActionKedisiplinanDialog({
    open,
    case: disciplineCase,
    options,
    onOpenChange,
}: DialogProps & {
    options: StudentDisciplineIndexPageProps['options'];
}) {
    const form = useForm<StudentDisciplineActionPayload>({
        action_plan: '',
        assigned_employee_id:
            disciplineCase.assigned_employee_id ??
            options.officers[0]?.value ??
            null,
    });
    const errors = form.errors as Partial<
        Record<keyof StudentDisciplineActionPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            assigned_employee_id:
                data.assigned_employee_id === 'none'
                    ? null
                    : data.assigned_employee_id,
        }));
        form.patch(
            routeOr(
                `/pesantrian/student-discipline-cases/${disciplineCase.id}/assign-action`,
                'pesantrian.student-discipline-cases.assign-action',
                disciplineCase.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            },
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Tetapkan tindakan pembinaan</DialogTitle>
                    <DialogDescription>
                        Tindakan hanya bisa ditetapkan setelah kasus masuk
                        status dalam review.
                    </DialogDescription>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <KedisiplinanFieldError message={errors.status} />
                    ) : null}
                    <KedisiplinanTextareaField
                        id="student-discipline-action-plan"
                        label="Rencana tindakan"
                        value={form.data.action_plan}
                        error={errors.action_plan}
                        placeholder="Contoh: refleksi tertulis dan pembinaan wali kelas"
                        required
                        onChange={(value) => form.setData('action_plan', value)}
                    />
                    <KedisiplinanSelectField
                        id="student-discipline-action-officer"
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
                        <SelectItem value="none">Belum ditetapkan</SelectItem>
                        {options.officers.map((officer) => (
                            <SelectItem
                                key={officer.value}
                                value={officer.value}
                            >
                                {officer.label}
                            </SelectItem>
                        ))}
                    </KedisiplinanSelectField>
                    <LifecycleFooter
                        processing={form.processing}
                        submitLabel="Tetapkan tindakan"
                        onCancel={() => onOpenChange(false)}
                    />
                </form>
            </DialogContent>
        </Dialog>
    );
}

export function ResolveKedisiplinanDialog({
    open,
    case: disciplineCase,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentDisciplineResolvePayload>({
        resolution_note: '',
    });
    const errors = form.errors as Partial<
        Record<keyof StudentDisciplineResolvePayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/student-discipline-cases/${disciplineCase.id}/resolve`,
                'pesantrian.student-discipline-cases.resolve',
                disciplineCase.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            },
        );
    };

    return (
        <TextLifecycleDialog
            open={open}
            title="Selesaikan kasus"
            description="Pastikan tindakan pembinaan sudah selesai sebelum kasus dibuat final."
            label="Catatan penyelesaian"
            placeholder="Contoh: santri sudah menyelesaikan pembinaan"
            value={form.data.resolution_note}
            error={errors.resolution_note ?? errors.status}
            processing={form.processing}
            submitLabel="Selesaikan kasus"
            onChange={(value) => form.setData('resolution_note', value)}
            onSubmit={submit}
            onCancel={() => onOpenChange(false)}
        />
    );
}

export function VoidKedisiplinanDialog({
    open,
    case: disciplineCase,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentDisciplineVoidPayload>({ void_reason: '' });
    const errors = form.errors as Partial<
        Record<keyof StudentDisciplineVoidPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/student-discipline-cases/${disciplineCase.id}/void`,
                'pesantrian.student-discipline-cases.void',
                disciplineCase.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            },
        );
    };

    return (
        <TextLifecycleDialog
            open={open}
            title="Batalkan kasus"
            description="Data tidak dihapus. Status akan menjadi void dan alasan tersimpan untuk audit."
            label="Alasan pembatalan"
            placeholder="Contoh: kasus salah input dan akan dibuat ulang"
            value={form.data.void_reason}
            error={errors.void_reason ?? errors.status}
            processing={form.processing}
            submitLabel="Batalkan kasus"
            destructive
            onChange={(value) => form.setData('void_reason', value)}
            onSubmit={submit}
            onCancel={() => onOpenChange(false)}
        />
    );
}

function SimpleLifecycleDialog({
    open,
    case: disciplineCase,
    title,
    description,
    confirmLabel,
    routeName,
    fallback,
    icon: Icon,
    onOpenChange,
}: DialogProps & {
    title: string;
    description: string;
    confirmLabel: string;
    routeName: Exclude<RouteName, undefined>;
    fallback: string;
    icon: LucideIcon;
}) {
    const form = useForm({});

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(routeOr(fallback, routeName, disciplineCase.id), {
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
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            <Icon className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>{title}</DialogTitle>
                            <DialogDescription className="mt-1">
                                {description}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {'status' in form.errors ? (
                        <KedisiplinanFieldError
                            message={String(form.errors.status)}
                        />
                    ) : null}
                    <p className="rounded-xl border bg-muted/40 p-3 text-sm text-foreground/70">
                        {disciplineCase.case_no} -{' '}
                        {disciplineCase.student_name} - status sekarang{' '}
                        {disciplineStatusLabel(disciplineCase.status)}
                    </p>
                    <LifecycleFooter
                        processing={form.processing}
                        submitLabel={confirmLabel}
                        onCancel={() => onOpenChange(false)}
                    />
                </form>
            </DialogContent>
        </Dialog>
    );
}

function TextLifecycleDialog({
    open,
    title,
    description,
    label,
    placeholder,
    value,
    error,
    processing,
    submitLabel,
    destructive = false,
    onChange,
    onSubmit,
    onCancel,
}: {
    open: boolean;
    title: string;
    description: string;
    label: string;
    placeholder: string;
    value: string;
    error?: string;
    processing: boolean;
    submitLabel: string;
    destructive?: boolean;
    onChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onCancel: () => void;
}) {
    return (
        <Dialog open={open} onOpenChange={(nextOpen) => !processing && !nextOpen && onCancel()}>
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--amber flex size-10 items-center justify-center rounded-lg">
                            {destructive ? (
                                <Ban className="size-5" aria-hidden="true" />
                            ) : (
                                <ClipboardCheck
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </span>
                        <div>
                            <DialogTitle>{title}</DialogTitle>
                            <DialogDescription className="mt-1">
                                {description}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={onSubmit}>
                    <KedisiplinanTextareaField
                        id={`student-discipline-${submitLabel}`}
                        label={label}
                        value={value}
                        error={error}
                        placeholder={placeholder}
                        required
                        onChange={onChange}
                    />
                    <LifecycleFooter
                        processing={processing}
                        submitLabel={submitLabel}
                        destructive={destructive}
                        onCancel={onCancel}
                    />
                </form>
            </DialogContent>
        </Dialog>
    );
}

function LifecycleFooter({
    processing,
    submitLabel,
    destructive = false,
    onCancel,
}: {
    processing: boolean;
    submitLabel: string;
    destructive?: boolean;
    onCancel: () => void;
}) {
    return (
        <DialogFooter>
            <Button
                type="button"
                variant="outline"
                disabled={processing}
                onClick={onCancel}
            >
                Batal
            </Button>
            <LoadingButton
                type="submit"
                variant={destructive ? 'destructive' : 'default'}
                loading={processing}
            >
                {destructive ? (
                    <Ban className="size-4" aria-hidden="true" />
                ) : (
                    <CheckCircle2 className="size-4" aria-hidden="true" />
                )}
                {submitLabel}
            </LoadingButton>
        </DialogFooter>
    );
}
