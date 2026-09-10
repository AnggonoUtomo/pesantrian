import { Link } from '@inertiajs/react';
import { ArrowLeft, CalendarDays, History, UserRound } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type {
    StudentAchievement,
    StudentAchievementIndexPageProps,
} from '../types';
import {
    achievementTypeLabel,
    formatDate,
    formatDateTime,
    lifecycleSummary,
} from './prestasiSantriDisplay';
import {
    RequestRevisionPrestasiDialog,
    SubmitPrestasiDialog,
    VerifyPrestasiDialog,
    VoidPrestasiDialog,
} from './PrestasiSantriLifecycleDialogs';
import { PrestasiSantriMutationDialog } from './PrestasiSantriMutationDialog';
import {
    PrestasiSantriLevelBadge,
    PrestasiSantriStatusBadge,
} from './PrestasiSantriStatusBadge';

type Props = {
    achievement: StudentAchievement;
    options: StudentAchievementIndexPageProps['options'];
    canManage: boolean;
    canRecord: boolean;
    canVerify: boolean;
    canArchive: boolean;
};

export function PrestasiSantriDetailPanel({
    achievement,
    options,
    canManage,
    canRecord,
    canVerify,
    canArchive,
}: Props) {
    const [mutationDialogOpen, setMutationDialogOpen] = useState(false);
    const [submitOpen, setSubmitOpen] = useState(false);
    const [verifyOpen, setVerifyOpen] = useState(false);
    const [revisionOpen, setRevisionOpen] = useState(false);
    const [voidOpen, setVoidOpen] = useState(false);
    const canEdit =
        canRecord &&
        (achievement.status === 'draft' ||
            achievement.status === 'needs_revision');
    const canSubmit = canEdit;
    const canVerifyCurrent = canVerify && achievement.status === 'submitted';
    const canVoid = canArchive && !achievement.summary.is_final;

    return (
        <>
            <div className="space-y-5">
                <section className="dashboard-card dashboard-card--yellow rounded-2xl border p-4 sm:p-5">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <Button asChild variant="outline" size="sm">
                                <Link
                                    href={routeOr(
                                        '/pesantrian/prestasi-santri',
                                        'pesantrian.prestasi-santri.index',
                                    )}
                                    className="gap-2"
                                >
                                    <ArrowLeft
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    Kembali
                                </Link>
                            </Button>
                            <p className="mt-4 text-sm text-foreground/55">
                                {achievement.achievement_no}
                            </p>
                            <h2 className="mt-1 text-xl font-semibold">
                                {achievement.title}
                            </h2>
                            <p className="mt-2 max-w-3xl text-sm text-foreground/65">
                                {achievement.description ??
                                    'Belum ada deskripsi prestasi.'}
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <PrestasiSantriStatusBadge
                                status={achievement.status}
                            />
                            <PrestasiSantriLevelBadge
                                level={achievement.level}
                            />
                        </div>
                    </div>
                </section>

                <section className="flex flex-col gap-3 rounded-2xl border bg-background/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="font-semibold">Aksi Prestasi</h2>
                        <p className="text-sm text-foreground/65">
                            Tombol disesuaikan dengan permission dan status
                            prestasi saat ini.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {canEdit ? (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setMutationDialogOpen(true)}
                            >
                                Edit
                            </Button>
                        ) : null}
                        {canSubmit ? (
                            <Button
                                type="button"
                                onClick={() => setSubmitOpen(true)}
                            >
                                Submit prestasi
                            </Button>
                        ) : null}
                        {canVerifyCurrent ? (
                            <>
                                <Button
                                    type="button"
                                    onClick={() => setVerifyOpen(true)}
                                >
                                    Verifikasi prestasi
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setRevisionOpen(true)}
                                >
                                    Minta revisi
                                </Button>
                            </>
                        ) : null}
                        {canVoid ? (
                            <Button
                                type="button"
                                variant="destructive"
                                onClick={() => setVoidOpen(true)}
                            >
                                Batalkan prestasi
                            </Button>
                        ) : null}
                    </div>
                </section>

                <div className="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <SectionTitle
                            icon={CalendarDays}
                            title="Detail Prestasi"
                        />
                        <dl className="grid gap-3 sm:grid-cols-2">
                            <DetailItem
                                label="Jenis"
                                value={achievementTypeLabel(
                                    achievement.achievement_type,
                                )}
                            />
                            <DetailItem
                                label="Hasil"
                                value={achievement.result}
                            />
                            <DetailItem
                                label="Kegiatan"
                                value={achievement.event_name ?? '-'}
                            />
                            <DetailItem
                                label="Penyelenggara"
                                value={achievement.organizer ?? '-'}
                            />
                            <DetailItem
                                label="Lokasi"
                                value={achievement.event_location ?? '-'}
                            />
                            <DetailItem
                                label="Tanggal prestasi"
                                value={formatDate(achievement.achieved_on)}
                            />
                            <DetailItem
                                label="Awal periode"
                                value={formatDate(
                                    achievement.period_started_on,
                                )}
                            />
                            <DetailItem
                                label="Akhir periode"
                                value={formatDate(achievement.period_ended_on)}
                            />
                        </dl>
                    </section>

                    <section className="dashboard-card dashboard-card--green space-y-4 rounded-2xl border p-4 sm:p-5">
                        <SectionTitle
                            icon={UserRound}
                            title="Santri dan kategori"
                        />
                        <dl className="space-y-3">
                            <DetailItem
                                label="Santri"
                                value={`${achievement.student_name} (${achievement.student_no})`}
                            />
                            <DetailItem
                                label="Kategori"
                                value={achievement.category.name}
                            />
                            <DetailItem
                                label="Periode akademik"
                                value={achievement.academic_period_label ?? '-'}
                            />
                            <DetailItem
                                label="Pembimbing"
                                value={achievement.mentor_name ?? '-'}
                            />
                        </dl>
                    </section>
                </div>

                <section className="dashboard-card dashboard-card--amber space-y-4 rounded-2xl border p-4 sm:p-5">
                    <SectionTitle icon={History} title="Lifecycle prestasi" />
                    <p className="text-sm text-foreground/65">
                        {lifecycleSummary(achievement.summary)}
                    </p>
                    <dl className="grid gap-3 md:grid-cols-3">
                        <DetailItem
                            label="Dikirim"
                            value={formatDateTime(achievement.submitted_at)}
                        />
                        <DetailItem
                            label="Diverifikasi"
                            value={formatDateTime(achievement.verified_at)}
                        />
                        <DetailItem
                            label="Dibatalkan"
                            value={formatDateTime(achievement.voided_at)}
                        />
                    </dl>
                    {achievement.verification_note ? (
                        <Note
                            title="Catatan verifikasi"
                            body={achievement.verification_note}
                        />
                    ) : null}
                    {achievement.void_reason ? (
                        <Note
                            title="Alasan batal"
                            body={achievement.void_reason}
                        />
                    ) : null}
                    <div className="flex flex-wrap gap-2">
                        <PermissionBadge active={canManage} label="Kelola" />
                        <PermissionBadge active={canRecord} label="Catat" />
                        <PermissionBadge
                            active={canVerify}
                            label="Verifikasi"
                        />
                        <PermissionBadge active={canArchive} label="Arsip" />
                    </div>
                </section>

                <section className="dashboard-card dashboard-card--slate space-y-4 rounded-2xl border p-4 sm:p-5">
                    <SectionTitle icon={History} title="Histori revisi" />
                    {achievement.revisions &&
                    achievement.revisions.length > 0 ? (
                        <div className="space-y-3">
                            {achievement.revisions.map((revision) => (
                                <article
                                    key={revision.id}
                                    className="rounded-xl border bg-background p-3"
                                >
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p className="font-medium">
                                            {revision.from_status
                                                ? `${revision.from_status} → ${revision.to_status}`
                                                : revision.to_status}
                                        </p>
                                        <span className="text-xs text-foreground/55">
                                            {formatDateTime(
                                                revision.changed_at,
                                            )}
                                        </span>
                                    </div>
                                    <p className="mt-2 text-sm text-foreground/65">
                                        {revision.reason}
                                    </p>
                                </article>
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-foreground/65">
                            Belum ada histori revisi untuk prestasi ini.
                        </p>
                    )}
                </section>
            </div>
            <PrestasiSantriMutationDialog
                open={mutationDialogOpen}
                achievement={achievement}
                options={options}
                onOpenChange={setMutationDialogOpen}
            />
            <SubmitPrestasiDialog
                open={submitOpen}
                achievement={achievement}
                onOpenChange={setSubmitOpen}
            />
            <VerifyPrestasiDialog
                open={verifyOpen}
                achievement={achievement}
                onOpenChange={setVerifyOpen}
            />
            <RequestRevisionPrestasiDialog
                open={revisionOpen}
                achievement={achievement}
                onOpenChange={setRevisionOpen}
            />
            <VoidPrestasiDialog
                open={voidOpen}
                achievement={achievement}
                onOpenChange={setVoidOpen}
            />
        </>
    );
}

function SectionTitle({
    icon: Icon,
    title,
}: {
    icon: typeof CalendarDays;
    title: string;
}) {
    return (
        <div className="flex items-center gap-2">
            <Icon className="size-5 text-foreground/45" aria-hidden="true" />
            <h2 className="font-semibold">{title}</h2>
        </div>
    );
}

function DetailItem({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="text-xs font-medium text-foreground/55">{label}</dt>
            <dd className="mt-1 text-sm">{value}</dd>
        </div>
    );
}

function Note({ title, body }: { title: string; body: string }) {
    return (
        <div className="rounded-xl border bg-background p-3">
            <p className="text-xs font-medium text-foreground/55">{title}</p>
            <p className="mt-1 text-sm">{body}</p>
        </div>
    );
}

function PermissionBadge({
    active,
    label,
}: {
    active: boolean;
    label: string;
}) {
    return (
        <Badge
            variant="outline"
            className={
                active
                    ? 'border-emerald-300 bg-background text-foreground'
                    : 'border-slate-300 bg-background text-foreground'
            }
        >
            {label}
        </Badge>
    );
}
