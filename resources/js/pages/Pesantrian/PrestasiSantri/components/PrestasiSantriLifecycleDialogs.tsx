import { useForm } from '@inertiajs/react';
import { Archive, Ban, CheckCircle2, Send, Undo2 } from 'lucide-react';
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
import { routeOr } from '@/lib/route';
import type { RouteName } from '@/lib/route';
import type {
    StudentAchievement,
    StudentAchievementCategory,
    StudentAchievementCategoryArchivePayload,
    StudentAchievementRevisionPayload,
    StudentAchievementVerifyPayload,
    StudentAchievementVoidPayload,
} from '../types';
import { achievementStatusLabel } from './prestasiSantriDisplay';
import {
    PrestasiFieldError,
    PrestasiTextareaField,
} from './PrestasiSantriFormFields';

type DialogProps = {
    open: boolean;
    achievement: StudentAchievement;
    onOpenChange: (open: boolean) => void;
};

export function SubmitPrestasiDialog({
    open,
    achievement,
    onOpenChange,
}: DialogProps) {
    return (
        <SimpleLifecycleDialog
            open={open}
            achievement={achievement}
            title="Submit prestasi"
            description="Draft prestasi akan diajukan untuk verifikasi."
            confirmLabel="Submit prestasi"
            routeName="pesantrian.prestasi-santri.submit"
            fallback={`/pesantrian/prestasi-santri/${achievement.id}/submit`}
            icon={Send}
            onOpenChange={onOpenChange}
        />
    );
}

export function VerifyPrestasiDialog({
    open,
    achievement,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentAchievementVerifyPayload>({
        verification_note: '',
    });
    const errors = form.errors as Partial<
        Record<keyof StudentAchievementVerifyPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.transform((data) => ({
            verification_note:
                data.verification_note?.trim() === ''
                    ? null
                    : data.verification_note,
        }));
        form.patch(
            routeOr(
                `/pesantrian/prestasi-santri/${achievement.id}/verify`,
                'pesantrian.prestasi-santri.verify',
                achievement.id,
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
            title="Verifikasi prestasi"
            description="Pastikan bukti prestasi sudah dicek sebelum disahkan."
            label="Catatan verifikasi"
            placeholder="Opsional, contoh: piagam sudah dicek oleh pembina"
            value={form.data.verification_note ?? ''}
            error={errors.verification_note ?? errors.status}
            processing={form.processing}
            submitLabel="Verifikasi prestasi"
            icon={CheckCircle2}
            onChange={(value) => form.setData('verification_note', value)}
            onSubmit={submit}
            onCancel={() => onOpenChange(false)}
        />
    );
}

export function RequestRevisionPrestasiDialog({
    open,
    achievement,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentAchievementRevisionPayload>({
        verification_note: '',
    });
    const errors = form.errors as Partial<
        Record<keyof StudentAchievementRevisionPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/prestasi-santri/${achievement.id}/revise`,
                'pesantrian.prestasi-santri.revise',
                achievement.id,
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
            title="Minta revisi prestasi"
            description="Prestasi akan dikembalikan ke pencatat untuk dilengkapi."
            label="Alasan revisi"
            placeholder="Contoh: bukti piagam perlu dilampirkan ulang"
            value={form.data.verification_note}
            error={errors.verification_note ?? errors.status}
            processing={form.processing}
            submitLabel="Minta revisi"
            icon={Undo2}
            onChange={(value) => form.setData('verification_note', value)}
            onSubmit={submit}
            onCancel={() => onOpenChange(false)}
        />
    );
}

export function VoidPrestasiDialog({
    open,
    achievement,
    onOpenChange,
}: DialogProps) {
    const form = useForm<StudentAchievementVoidPayload>({ void_reason: '' });
    const errors = form.errors as Partial<
        Record<keyof StudentAchievementVoidPayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/prestasi-santri/${achievement.id}/void`,
                'pesantrian.prestasi-santri.void',
                achievement.id,
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
            title="Batalkan prestasi"
            description="Data tidak dihapus. Status akan menjadi void dan alasan tersimpan untuk audit."
            label="Alasan pembatalan"
            placeholder="Contoh: catatan prestasi salah input"
            value={form.data.void_reason}
            error={errors.void_reason ?? errors.status}
            processing={form.processing}
            submitLabel="Batalkan prestasi"
            icon={Ban}
            destructive
            onChange={(value) => form.setData('void_reason', value)}
            onSubmit={submit}
            onCancel={() => onOpenChange(false)}
        />
    );
}

export function ArchivePrestasiCategoryDialog({
    open,
    category,
    onOpenChange,
}: {
    open: boolean;
    category: StudentAchievementCategory;
    onOpenChange: (open: boolean) => void;
}) {
    const form = useForm<StudentAchievementCategoryArchivePayload>({
        reason: '',
    });
    const errors = form.errors as Partial<
        Record<keyof StudentAchievementCategoryArchivePayload | 'status', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            routeOr(
                `/pesantrian/prestasi-santri/categories/${category.id}/archive`,
                'pesantrian.prestasi-santri.categories.archive',
                category.id,
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
            title="Arsipkan kategori prestasi"
            description={`Kategori ${category.name} tidak dihapus, hanya disembunyikan dari pilihan aktif.`}
            label="Alasan arsip"
            placeholder="Contoh: kategori digabung ke kategori lain"
            value={form.data.reason}
            error={errors.reason ?? errors.status}
            processing={form.processing}
            submitLabel="Arsipkan kategori"
            icon={Archive}
            destructive
            onChange={(value) => form.setData('reason', value)}
            onSubmit={submit}
            onCancel={() => onOpenChange(false)}
        />
    );
}

function SimpleLifecycleDialog({
    open,
    achievement,
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
        form.patch(routeOr(fallback, routeName, achievement.id), {
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
                        <span className="dashboard-icon dashboard-accent--yellow flex size-10 items-center justify-center rounded-lg">
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
                    <div className="rounded-xl border bg-muted/35 p-4 text-sm">
                        <p className="font-medium">{achievement.title}</p>
                        <p className="mt-1 text-foreground/65">
                            Status saat ini:{' '}
                            {achievementStatusLabel(achievement.status)}
                        </p>
                    </div>
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
                            {confirmLabel}
                        </LoadingButton>
                    </DialogFooter>
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
    icon: Icon,
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
    icon: LucideIcon;
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
                        <span
                            className={
                                destructive
                                    ? 'dashboard-icon dashboard-accent--red flex size-10 items-center justify-center rounded-lg'
                                    : 'dashboard-icon dashboard-accent--yellow flex size-10 items-center justify-center rounded-lg'
                            }
                        >
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
                <form className="space-y-4" onSubmit={onSubmit}>
                    {error ? <PrestasiFieldError message={error} /> : null}
                    <PrestasiTextareaField
                        id={`student-achievement-${title.toLowerCase().replace(/\s+/g, '-')}`}
                        label={label}
                        value={value}
                        placeholder={placeholder}
                        required={destructive || submitLabel !== 'Verifikasi prestasi'}
                        onChange={onChange}
                    />
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
                            loading={processing}
                            variant={destructive ? 'destructive' : 'default'}
                        >
                            {submitLabel}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
