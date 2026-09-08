import { Link } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2, ClipboardList, History } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type {
    StudentDisciplineCase,
    StudentDisciplineIndexPageProps,
} from '../types';
import {
    disciplineSeverityLabel,
    formatDate,
    formatDateTime,
    lifecycleSummary,
    pointsLabel,
} from './kedisiplinanSantriDisplay';
import {
    AssignActionKedisiplinanDialog,
    ResolveKedisiplinanDialog,
    ReviewKedisiplinanDialog,
    SubmitKedisiplinanDialog,
    VoidKedisiplinanDialog,
} from './KedisiplinanSantriLifecycleDialogs';
import { KedisiplinanSantriMutationDialog } from './KedisiplinanSantriMutationDialog';
import { KedisiplinanSantriStatusBadge } from './KedisiplinanSantriStatusBadge';

type Props = {
    case: StudentDisciplineCase;
    options: StudentDisciplineIndexPageProps['options'];
    canManage: boolean;
    canReview: boolean;
    canResolve: boolean;
    canArchive: boolean;
};

export function KedisiplinanSantriDetailPanel({
    case: disciplineCase,
    options,
    canManage,
    canReview,
    canResolve,
    canArchive,
}: Props) {
    const [mutationDialogOpen, setMutationDialogOpen] = useState(false);
    const [submitOpen, setSubmitOpen] = useState(false);
    const [reviewOpen, setReviewOpen] = useState(false);
    const [assignActionOpen, setAssignActionOpen] = useState(false);
    const [resolveOpen, setResolveOpen] = useState(false);
    const [voidOpen, setVoidOpen] = useState(false);
    const isFinal = disciplineCase.summary.is_final;

    return (
        <>
            <div className="space-y-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Button asChild variant="outline" size="sm">
                        <Link
                            href={String(
                                routeOr(
                                    '/pesantrian/student-discipline-cases',
                                    'pesantrian.student-discipline-cases.index',
                                ),
                            )}
                        >
                            <ArrowLeft className="size-4" aria-hidden="true" />
                            Kembali ke daftar
                        </Link>
                    </Button>
                    <div className="flex flex-wrap gap-2">
                        {canManage ? (
                            <Badge variant="outline">Kelola</Badge>
                        ) : null}
                        {canReview ? (
                            <Badge variant="outline">Review</Badge>
                        ) : null}
                        {canResolve ? (
                            <Badge variant="outline">Penyelesaian</Badge>
                        ) : null}
                        {canArchive ? (
                            <Badge variant="outline">Arsip</Badge>
                        ) : null}
                    </div>
                </div>

                <section className="flex flex-col gap-3 rounded-2xl border bg-background/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="font-semibold">Aksi Kedisiplinan</h2>
                        <p className="text-sm text-foreground/65">
                            Tombol disesuaikan dengan permission dan status
                            kasus saat ini.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {canManage && disciplineCase.status === 'draft' ? (
                            <>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setMutationDialogOpen(true)}
                                >
                                    Edit
                                </Button>
                                <Button
                                    type="button"
                                    onClick={() => setSubmitOpen(true)}
                                >
                                    Submit kasus
                                </Button>
                            </>
                        ) : null}
                        {canReview && disciplineCase.status === 'submitted' ? (
                            <Button
                                type="button"
                                onClick={() => setReviewOpen(true)}
                            >
                                Review kasus
                            </Button>
                        ) : null}
                        {canReview && disciplineCase.status === 'in_review' ? (
                            <Button
                                type="button"
                                onClick={() => setAssignActionOpen(true)}
                            >
                                Tetapkan tindakan
                            </Button>
                        ) : null}
                        {canResolve &&
                        disciplineCase.status === 'action_assigned' ? (
                            <Button
                                type="button"
                                onClick={() => setResolveOpen(true)}
                            >
                                Selesaikan kasus
                            </Button>
                        ) : null}
                        {canResolve && !isFinal ? (
                            <Button
                                type="button"
                                variant="destructive"
                                onClick={() => setVoidOpen(true)}
                            >
                                Batalkan kasus
                            </Button>
                        ) : null}
                    </div>
                </section>

                <section className="dashboard-card dashboard-card--amber rounded-2xl border p-4 sm:p-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p className="text-sm text-foreground/60">
                            Detail Kedisiplinan Santri
                        </p>
                        <h2 className="mt-1 text-2xl font-semibold">
                            {disciplineCase.case_no}
                        </h2>
                        <p className="mt-2 max-w-3xl text-sm text-foreground/65">
                            {disciplineCase.description}
                        </p>
                    </div>
                    <KedisiplinanSantriStatusBadge
                        status={disciplineCase.status}
                    />
                </div>

                <div className="mt-5 grid gap-4 lg:grid-cols-3">
                    <InfoCard
                        icon={ClipboardList}
                        title="Informasi kasus"
                        rows={[
                            ['Tanggal kejadian', formatDateTime(disciplineCase.occurred_at)],
                            ['Lokasi', disciplineCase.location ?? '-'],
                            ['Dilaporkan oleh', disciplineCase.reported_by ?? '-'],
                            ['Dibuat pada', formatDateTime(disciplineCase.created_at)],
                        ]}
                    />
                    <InfoCard
                        icon={CheckCircle2}
                        title="Santri dan kategori"
                        rows={[
                            ['Santri', disciplineCase.student_name],
                            ['NIS', disciplineCase.student_no],
                            ['Unit', disciplineCase.unit_name ?? '-'],
                            ['Kategori', disciplineCase.category.name],
                            ['Tingkat', disciplineSeverityLabel(disciplineCase.severity)],
                            ['Poin', pointsLabel(disciplineCase.points)],
                        ]}
                    />
                    <InfoCard
                        icon={History}
                        title="Lifecycle kasus"
                        rows={[
                            ['Ringkasan', lifecycleSummary(disciplineCase.summary)],
                            ['Submit', formatDateTime(disciplineCase.submitted_at)],
                            ['Review', formatDateTime(disciplineCase.reviewed_at)],
                            ['Tindakan', formatDateTime(disciplineCase.action_assigned_at)],
                            ['Selesai', formatDateTime(disciplineCase.resolved_at)],
                            ['Dibatalkan', formatDateTime(disciplineCase.voided_at)],
                        ]}
                    />
                </div>
                </section>

                <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
                <h2 className="font-semibold">Catatan pembinaan</h2>
                <div className="mt-4 grid gap-4 lg:grid-cols-3">
                    <DetailText
                        label="Catatan review"
                        value={disciplineCase.review_note}
                    />
                    <DetailText
                        label="Rencana tindakan"
                        value={disciplineCase.action_plan}
                    />
                    <DetailText
                        label="Catatan penyelesaian"
                        value={disciplineCase.resolution_note}
                    />
                </div>
                </section>

                <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h2 className="font-semibold">Histori revisi</h2>
                        <p className="text-sm text-foreground/60">
                            Perubahan penting dicatat supaya alur kasus tetap
                            dapat diaudit.
                        </p>
                    </div>
                    <Badge variant="outline">
                        {disciplineCase.summary.revision_count} revisi
                    </Badge>
                </div>

                {disciplineCase.revisions &&
                disciplineCase.revisions.length > 0 ? (
                    <div className="mt-4 divide-y rounded-xl border bg-background">
                        {disciplineCase.revisions.map((revision) => (
                            <article key={revision.id} className="p-4">
                                <div className="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h3 className="font-medium">
                                            {revision.reason}
                                        </h3>
                                        <p className="text-sm text-foreground/60">
                                            Oleh {revision.changed_by ?? '-'}
                                        </p>
                                    </div>
                                    <time className="text-sm text-foreground/60">
                                        {formatDate(revision.changed_at)}
                                    </time>
                                </div>
                            </article>
                        ))}
                    </div>
                ) : (
                    <p className="mt-4 rounded-xl border border-dashed p-4 text-sm text-foreground/60">
                        Belum ada histori revisi untuk kasus ini.
                    </p>
                )}
                </section>
            </div>

            <KedisiplinanSantriMutationDialog
                open={mutationDialogOpen}
                case={disciplineCase}
                options={options}
                onOpenChange={setMutationDialogOpen}
            />
            <SubmitKedisiplinanDialog
                open={submitOpen}
                case={disciplineCase}
                onOpenChange={setSubmitOpen}
            />
            <ReviewKedisiplinanDialog
                open={reviewOpen}
                case={disciplineCase}
                onOpenChange={setReviewOpen}
            />
            <AssignActionKedisiplinanDialog
                open={assignActionOpen}
                case={disciplineCase}
                options={options}
                onOpenChange={setAssignActionOpen}
            />
            <ResolveKedisiplinanDialog
                open={resolveOpen}
                case={disciplineCase}
                onOpenChange={setResolveOpen}
            />
            <VoidKedisiplinanDialog
                open={voidOpen}
                case={disciplineCase}
                onOpenChange={setVoidOpen}
            />
        </>
    );
}

function InfoCard({
    icon: Icon,
    title,
    rows,
}: {
    icon: typeof ClipboardList;
    title: string;
    rows: [string, string][];
}) {
    return (
        <section className="rounded-xl border bg-background p-4">
            <div className="flex items-center gap-2">
                <Icon className="size-4 text-amber-600" aria-hidden="true" />
                <h3 className="font-medium">{title}</h3>
            </div>
            <dl className="mt-4 space-y-2 text-sm">
                {rows.map(([label, value]) => (
                    <div key={label} className="flex justify-between gap-3">
                        <dt className="text-foreground/55">{label}</dt>
                        <dd className="text-right font-medium">{value}</dd>
                    </div>
                ))}
            </dl>
        </section>
    );
}

function DetailText({
    label,
    value,
}: {
    label: string;
    value: string | null;
}) {
    return (
        <section className="rounded-xl border bg-background p-4">
            <h3 className="text-sm font-medium text-foreground/60">{label}</h3>
            <p className="mt-2 text-sm">{value ?? '-'}</p>
        </section>
    );
}
