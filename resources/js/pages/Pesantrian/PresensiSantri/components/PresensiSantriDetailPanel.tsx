import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { StudentAttendance, StudentAttendanceShowPageProps } from '../types';
import {
    contextLabel,
    entryStatusLabel,
    followUpCount,
} from './presensiSantriDisplay';
import { PresensiSantriEntryEditor } from './PresensiSantriEntryEditor';
import {
    ReasonPresensiSantriDialog,
    SubmitPresensiSantriDialog,
} from './PresensiSantriLifecycleDialogs';
import { PresensiSantriMutationDialog } from './PresensiSantriMutationDialog';
import { PresensiSantriStatusBadge } from './PresensiSantriStatusBadge';

type Props = {
    attendance: StudentAttendance;
    options: StudentAttendanceShowPageProps['options'];
    canManage: boolean;
    canSubmit: boolean;
    canRevise: boolean;
    canArchive: boolean;
};

export function PresensiSantriDetailPanel({
    attendance,
    options,
    canManage,
    canSubmit,
    canRevise,
    canArchive,
}: Props) {
    const entries = attendance.entries ?? [];
    const [editOpen, setEditOpen] = useState(false);
    const [submitOpen, setSubmitOpen] = useState(false);
    const [reviseOpen, setReviseOpen] = useState(false);
    const [voidOpen, setVoidOpen] = useState(false);
    const canEditSession =
        canManage && ['draft', 'revised'].includes(attendance.status);
    const canSubmitSession = canSubmit && attendance.status === 'draft';
    const canReviseSession =
        canRevise && ['submitted', 'revised'].includes(attendance.status);
    const canVoidSession = canArchive && attendance.status !== 'void';

    return (
        <div className="space-y-5">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <Button asChild variant="outline" size="sm">
                    <Link
                        href={String(
                            routeOr(
                                '/pesantrian/student-attendances',
                                'pesantrian.student-attendances.index',
                            ),
                        )}
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        Kembali ke daftar
                    </Link>
                </Button>
                <div className="flex flex-wrap gap-2">
                    {canEditSession ? (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => setEditOpen(true)}
                        >
                            Edit sesi presensi
                        </Button>
                    ) : null}
                    {canSubmitSession ? (
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            onClick={() => setSubmitOpen(true)}
                        >
                            Submit presensi
                        </Button>
                    ) : null}
                    {canReviseSession ? (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => setReviseOpen(true)}
                        >
                            Buka revisi
                        </Button>
                    ) : null}
                    {canVoidSession ? (
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            onClick={() => setVoidOpen(true)}
                        >
                            Batalkan sesi presensi
                        </Button>
                    ) : null}
                </div>
            </div>

            <section className="dashboard-card rounded-2xl border p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-sm text-foreground/60">
                            {contextLabel(attendance.context_type)} ·{' '}
                            {attendance.context_name}
                        </p>
                        <h2 className="mt-1 text-xl font-semibold">
                            {attendance.session_code} ·{' '}
                            {attendance.session_name}
                        </h2>
                        <p className="mt-1 text-sm text-foreground/65">
                            Tanggal presensi: {attendance.attendance_date}
                        </p>
                    </div>
                    <PresensiSantriStatusBadge status={attendance.status} />
                </div>
                {attendance.void_reason ? (
                    <p className="dashboard-message--error mt-4 rounded-xl border p-3 text-sm">
                        Alasan pembatalan: {attendance.void_reason}
                    </p>
                ) : null}
            </section>

            <section className="dashboard-card dashboard-card--blue rounded-2xl border p-5">
                <h2 className="font-semibold">Ringkasan status</h2>
                <dl className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    <SummaryItem
                        label="Total"
                        value={attendance.summary.total}
                    />
                    <SummaryItem
                        label="Hadir"
                        value={attendance.summary.present}
                    />
                    <SummaryItem
                        label="Terlambat"
                        value={attendance.summary.late}
                    />
                    <SummaryItem
                        label="Izin"
                        value={attendance.summary.excused}
                    />
                    <SummaryItem label="Sakit" value={attendance.summary.sick} />
                    <SummaryItem
                        label="Alfa"
                        value={attendance.summary.absent}
                    />
                </dl>
                <p className="mt-3 text-sm text-foreground/65">
                    {followUpCount(attendance.summary)} santri butuh tindak
                    lanjut dari operator atau pembina.
                </p>
            </section>

            <section className="dashboard-card rounded-2xl border p-5">
                <div className="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 className="font-semibold">Daftar santri</h2>
                        <p className="text-sm text-foreground/65">
                            NIS, nama, status kehadiran, keterlambatan, dan
                            catatan operator.
                        </p>
                    </div>
                    <p className="text-sm text-foreground/60">
                        {entries.length} entry
                    </p>
                </div>

                <div className="mt-4 overflow-hidden rounded-xl border">
                    <div className="hidden overflow-x-auto md:block">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                                <tr>
                                    <th scope="col" className="px-4 py-3">
                                        NIS
                                    </th>
                                    <th scope="col" className="px-4 py-3">
                                        Nama santri
                                    </th>
                                    <th scope="col" className="px-4 py-3">
                                        Status
                                    </th>
                                    <th scope="col" className="px-4 py-3">
                                        Catatan
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {entries.map((entry) => (
                                    <tr key={entry.id} className="bg-background">
                                        <td className="px-4 py-3 font-medium">
                                            {entry.student_no}
                                        </td>
                                        <td className="px-4 py-3 text-foreground/70">
                                            {entry.student_name}
                                        </td>
                                        <td className="px-4 py-3">
                                            <PresensiSantriStatusBadge
                                                type="entry"
                                                status={entry.status}
                                            />
                                        </td>
                                        <td className="px-4 py-3 text-foreground/70">
                                            {entry.status === 'late' &&
                                            entry.minutes_late
                                                ? `${entryStatusLabel(entry.status)} ${entry.minutes_late} menit`
                                                : (entry.note ?? '—')}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="divide-y md:hidden">
                        {entries.map((entry) => (
                            <article
                                key={entry.id}
                                className="space-y-2 bg-background p-4"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 className="font-medium">
                                            {entry.student_name}
                                        </h3>
                                        <p className="text-xs text-foreground/60">
                                            NIS {entry.student_no}
                                        </p>
                                    </div>
                                    <PresensiSantriStatusBadge
                                        type="entry"
                                        status={entry.status}
                                    />
                                </div>
                                <p className="text-sm text-foreground/65">
                                    {entry.note ??
                                        (entry.minutes_late
                                            ? `Terlambat ${entry.minutes_late} menit`
                                            : 'Tidak ada catatan.')}
                                </p>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            <PresensiSantriEntryEditor
                attendance={attendance}
                students={options.students}
                canManage={canManage}
            />

            <PresensiSantriMutationDialog
                open={editOpen}
                attendance={attendance}
                options={options}
                onOpenChange={setEditOpen}
            />
            <SubmitPresensiSantriDialog
                open={submitOpen}
                attendance={attendance}
                onOpenChange={setSubmitOpen}
            />
            <ReasonPresensiSantriDialog
                open={reviseOpen}
                attendance={attendance}
                mode="revise"
                onOpenChange={setReviseOpen}
            />
            <ReasonPresensiSantriDialog
                open={voidOpen}
                attendance={attendance}
                mode="void"
                onOpenChange={setVoidOpen}
            />
        </div>
    );
}

function SummaryItem({ label, value }: { label: string; value: number }) {
    return (
        <div className="rounded-xl border bg-background p-3">
            <dt className="text-xs text-foreground/55">{label}</dt>
            <dd className="mt-1 text-xl font-semibold">{value}</dd>
        </div>
    );
}
