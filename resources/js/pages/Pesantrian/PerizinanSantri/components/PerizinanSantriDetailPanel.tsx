import { Link } from '@inertiajs/react';
import { ArrowLeft, Clock, FileText, History, UserRound } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { StudentPermit } from '../types';
import {
    formatDateTime,
    lifecycleSummary,
    permitTypeLabel,
} from './perizinanSantriDisplay';
import { PerizinanSantriStatusBadge } from './PerizinanSantriStatusBadge';

type Props = {
    permit: StudentPermit;
    canManage: boolean;
    canApprove: boolean;
    canCheckout: boolean;
    canReturn: boolean;
    canArchive: boolean;
};

export function PerizinanSantriDetailPanel({
    permit,
    canManage,
    canApprove,
    canCheckout,
    canReturn,
    canArchive,
}: Props) {
    return (
        <div className="space-y-5">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Button asChild variant="outline" size="sm">
                    <Link
                        href={routeOr(
                            '/pesantrian/student-permits',
                            'pesantrian.student-permits.index',
                        )}
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        Kembali ke daftar
                    </Link>
                </Button>
                <div className="flex flex-wrap gap-2 text-xs text-foreground/60">
                    {canManage ? <span>Kelola draft aktif</span> : null}
                    {canApprove ? <span>Review aktif</span> : null}
                    {canCheckout ? <span>Check-out aktif</span> : null}
                    {canReturn ? <span>Return aktif</span> : null}
                    {canArchive ? <span>Void aktif</span> : null}
                </div>
            </div>

            <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4 sm:p-5">
                <div className="flex flex-col gap-3 border-b pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-sm text-foreground/60">
                            Detail Perizinan Santri
                        </p>
                        <h2 className="mt-1 text-xl font-semibold">
                            {permit.permit_no}
                        </h2>
                        <p className="text-sm text-foreground/65">
                            {permit.student_name} · {permit.student_no}
                        </p>
                    </div>
                    <PerizinanSantriStatusBadge status={permit.status} />
                </div>

                <div className="grid gap-5 pt-5 lg:grid-cols-3">
                    <DetailSection icon={FileText} title="Informasi izin">
                        <DetailItem
                            label="Jenis izin"
                            value={permitTypeLabel(permit.permit_type)}
                        />
                        <DetailItem label="Tujuan" value={permit.destination} />
                        <DetailItem label="Alasan" value={permit.reason} />
                        <DetailItem
                            label="Ringkasan"
                            value={lifecycleSummary(permit.summary)}
                        />
                    </DetailSection>

                    <DetailSection icon={UserRound} title="Santri dan wali">
                        <DetailItem label="NIS" value={permit.student_no} />
                        <DetailItem
                            label="Nama santri"
                            value={permit.student_name}
                        />
                        <DetailItem
                            label="Nama wali"
                            value={permit.guardian_name}
                        />
                        <DetailItem
                            label="Telepon wali"
                            value={permit.guardian_phone}
                        />
                        <DetailItem
                            label="Relasi wali"
                            value={permit.guardian_relation}
                        />
                    </DetailSection>

                    <DetailSection icon={Clock} title="Lifecycle waktu">
                        <DetailItem
                            label="Mulai izin"
                            value={formatDateTime(permit.starts_at)}
                        />
                        <DetailItem
                            label="Batas kembali"
                            value={formatDateTime(permit.ends_at)}
                        />
                        <DetailItem
                            label="Submit"
                            value={formatDateTime(permit.submitted_at)}
                        />
                        <DetailItem
                            label="Review"
                            value={formatDateTime(permit.reviewed_at)}
                        />
                        <DetailItem
                            label="Check-out"
                            value={formatDateTime(permit.checked_out_at)}
                        />
                        <DetailItem
                            label="Kembali"
                            value={formatDateTime(permit.returned_at)}
                        />
                        <DetailItem
                            label="Void"
                            value={formatDateTime(permit.voided_at)}
                        />
                    </DetailSection>
                </div>
            </section>

            <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
                <div className="flex items-center gap-2 border-b pb-3">
                    <History className="size-4 text-cyan-600" />
                    <h2 className="font-semibold">Histori revisi</h2>
                </div>
                {permit.revisions && permit.revisions.length > 0 ? (
                    <ol className="mt-4 space-y-3">
                        {permit.revisions.map((revision) => (
                            <li
                                key={revision.id}
                                className="rounded-xl border bg-background/60 p-3"
                            >
                                <p className="font-medium">
                                    {revision.reason}
                                </p>
                                <p className="mt-1 text-xs text-foreground/60">
                                    {formatDateTime(revision.changed_at)} ·
                                    actor {revision.changed_by ?? 'system'}
                                </p>
                            </li>
                        ))}
                    </ol>
                ) : (
                    <p className="mt-4 text-sm text-foreground/65">
                        Belum ada histori revisi untuk izin ini.
                    </p>
                )}
            </section>
        </div>
    );
}

function DetailSection({
    icon: Icon,
    title,
    children,
}: {
    icon: typeof FileText;
    title: string;
    children: ReactNode;
}) {
    return (
        <section className="space-y-3">
            <h3 className="flex items-center gap-2 font-medium">
                <Icon className="size-4 text-cyan-600" aria-hidden="true" />
                {title}
            </h3>
            <dl className="space-y-2 text-sm">{children}</dl>
        </section>
    );
}

function DetailItem({
    label,
    value,
}: {
    label: string;
    value: string | null | undefined;
}) {
    return (
        <div className="rounded-lg border bg-background/60 p-3">
            <dt className="text-xs text-foreground/55">{label}</dt>
            <dd className="mt-1 font-medium">{value || 'Belum diisi'}</dd>
        </div>
    );
}
