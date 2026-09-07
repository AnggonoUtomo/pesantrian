import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { TahfidzSubmission } from '../types';
import {
    memorizationRange,
    tahfidzTypeLabel,
    targetRange,
} from './tahfidzDisplay';
import { TahfidzStatusBadge } from './TahfidzStatusBadge';

type Props = {
    submission: TahfidzSubmission;
    canManage: boolean;
    canRecord: boolean;
    canReview: boolean;
    canArchive: boolean;
};

export function TahfidzDetailPanel({
    submission,
    canManage,
    canRecord,
    canReview,
    canArchive,
}: Props) {
    const revisions = submission.revisions ?? [];

    return (
        <div className="space-y-5">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <Button asChild variant="outline" size="sm">
                    <Link
                        href={String(
                            routeOr(
                                '/pesantrian/tahfidz',
                                'pesantrian.tahfidz.index',
                            ),
                        )}
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        Kembali ke daftar
                    </Link>
                </Button>
                <p className="text-sm text-foreground/60">
                    Aksi form program, target, setoran, review, dan void masuk
                    Increment 11. Hak akses aktif: {permissionSummary({
                        canManage,
                        canRecord,
                        canReview,
                        canArchive,
                    })}
                </p>
            </div>

            <section className="dashboard-card rounded-2xl border p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-sm text-foreground/60">
                            {submission.program.code} ·{' '}
                            {submission.program.name}
                        </p>
                        <h2 className="mt-1 text-xl font-semibold">
                            {submission.student_name}
                        </h2>
                        <p className="mt-1 text-sm text-foreground/65">
                            NIS {submission.student_no} ·{' '}
                            {tahfidzTypeLabel(submission.type)} ·{' '}
                            {submission.submission_date}
                        </p>
                    </div>
                    <TahfidzStatusBadge status={submission.status} />
                </div>
                {submission.void_reason ? (
                    <p className="dashboard-message--error mt-4 rounded-xl border p-3 text-sm">
                        Alasan pembatalan: {submission.void_reason}
                    </p>
                ) : null}
            </section>

            <section className="grid gap-4 lg:grid-cols-2">
                <InfoCard title="Detail setoran">
                    <DetailItem label="Rentang setoran" value={memorizationRange(submission)} />
                    <DetailItem
                        label="Pembimbing"
                        value={submission.supervisor_name ?? 'Belum diisi'}
                    />
                    <DetailItem
                        label="Catatan kualitas"
                        value={submission.quality_note ?? 'Belum ada catatan'}
                    />
                </InfoCard>
                <InfoCard title="Target hafalan">
                    <DetailItem label="Target" value={targetRange(submission)} />
                    <DetailItem
                        label="Periode"
                        value={submission.target?.period_label ?? 'Belum diisi'}
                    />
                    <DetailItem
                        label="Catatan target"
                        value={submission.target?.target_note ?? 'Belum ada catatan'}
                    />
                </InfoCard>
            </section>

            <section className="dashboard-card dashboard-card--blue rounded-2xl border p-5">
                <h2 className="font-semibold">Ringkasan review</h2>
                <dl className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryItem
                        label="Terhubung target"
                        value={submission.summary.has_target ? 'Ya' : 'Tidak'}
                    />
                    <SummaryItem
                        label="Ada pembimbing"
                        value={
                            submission.summary.has_supervisor ? 'Ya' : 'Tidak'
                        }
                    />
                    <SummaryItem
                        label="Ada revisi"
                        value={submission.summary.has_revision ? 'Ya' : 'Tidak'}
                    />
                    <SummaryItem
                        label="Jumlah revisi"
                        value={String(submission.summary.revision_count)}
                    />
                </dl>
            </section>

            <section className="dashboard-card rounded-2xl border p-5">
                <div className="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 className="font-semibold">Riwayat koreksi</h2>
                        <p className="text-sm text-foreground/65">
                            Jejak review, koreksi, atau pembatalan setoran
                            tahfidz.
                        </p>
                    </div>
                    <p className="text-sm text-foreground/60">
                        {revisions.length} catatan
                    </p>
                </div>
                {revisions.length > 0 ? (
                    <div className="mt-4 divide-y rounded-xl border">
                        {revisions.map((revision) => (
                            <article key={revision.id} className="p-4">
                                <p className="font-medium">{revision.reason}</p>
                                <p className="mt-1 text-sm text-foreground/60">
                                    {revision.changed_at}
                                </p>
                            </article>
                        ))}
                    </div>
                ) : (
                    <p className="mt-4 rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                        Belum ada riwayat koreksi untuk setoran ini.
                    </p>
                )}
            </section>
        </div>
    );
}

function InfoCard({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <section className="dashboard-card rounded-2xl border p-5">
            <h2 className="font-semibold">{title}</h2>
            <dl className="mt-4 space-y-3">{children}</dl>
        </section>
    );
}

function DetailItem({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="text-xs text-foreground/55">{label}</dt>
            <dd className="mt-1 text-sm font-medium text-foreground/80">
                {value}
            </dd>
        </div>
    );
}

function SummaryItem({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border bg-background p-3">
            <dt className="text-xs text-foreground/55">{label}</dt>
            <dd className="mt-1 text-xl font-semibold">{value}</dd>
        </div>
    );
}

function permissionSummary({
    canManage,
    canRecord,
    canReview,
    canArchive,
}: {
    canManage: boolean;
    canRecord: boolean;
    canReview: boolean;
    canArchive: boolean;
}): string {
    const labels = [
        canManage ? 'kelola' : null,
        canRecord ? 'catat' : null,
        canReview ? 'review' : null,
        canArchive ? 'arsip' : null,
    ].filter(Boolean);

    return labels.length > 0 ? labels.join(', ') : 'lihat saja';
}
