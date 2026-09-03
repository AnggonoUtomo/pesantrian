import { Link } from '@inertiajs/react';
import { Archive, ArrowLeft, RotateCcw, UserPlus, UserRoundCheck, UsersRound } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { ClassGroup, ClassGroupHomeroom, ClassGroupShowPageProps, ClassGroupStudent } from '../types';
import { ClassGroupArchiveDialog } from './ClassGroupArchiveDialog';
import { HomeroomAssignmentDialog } from './HomeroomAssignmentDialog';
import { referenceLabel } from './kelasRombelDisplay';
import { KelasRombelStatusBadge } from './KelasRombelStatusBadge';
import { StudentPlacementDialog } from './StudentPlacementDialog';

type Props = {
    classGroup: ClassGroup;
    options: ClassGroupShowPageProps['options'];
    canManage: boolean;
    canPlacement: boolean;
    canArchive: boolean;
};

export function KelasRombelDetailPanel({
    classGroup,
    options,
    canManage,
    canPlacement,
    canArchive,
}: Props) {
    const students = classGroup.students ?? [];
    const homerooms = classGroup.homerooms ?? [];
    const [placementOpen, setPlacementOpen] = useState(false);
    const [homeroomOpen, setHomeroomOpen] = useState(false);
    const [archiveOpen, setArchiveOpen] = useState(false);
    const [restoreOpen, setRestoreOpen] = useState(false);

    return (
        <div className="space-y-5">
            <Button asChild variant="outline" size="sm">
                <Link
                    href={routeOr(
                        '/academic/class-groups',
                        'academic.class-groups.index',
                    )}
                    prefetch
                >
                    <ArrowLeft className="size-4" aria-hidden="true" />
                    Kembali ke daftar
                </Link>
            </Button>

            <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4 sm:p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-sm text-foreground/65">
                            {classGroup.code}
                        </p>
                        <h2 className="text-xl font-semibold">
                            {classGroup.name}
                        </h2>
                        <p className="mt-1 text-sm text-foreground/65">
                            {classGroup.unit.name} ·{' '}
                            {classGroup.class_level.name}
                        </p>
                    </div>
                    <div className="flex flex-col gap-2 sm:items-end">
                        <KelasRombelStatusBadge status={classGroup.status} />
                        <div className="flex flex-wrap gap-2">
                            {canPlacement && classGroup.archived_at === null ? (
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    onClick={() => setPlacementOpen(true)}
                                >
                                    <UserPlus className="size-4" aria-hidden="true" />
                                    Tempatkan santri
                                </Button>
                            ) : null}
                            {canManage && classGroup.archived_at === null ? (
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    onClick={() => setHomeroomOpen(true)}
                                >
                                    <UserRoundCheck className="size-4" aria-hidden="true" />
                                    Tetapkan wali
                                </Button>
                            ) : null}
                            {canArchive ? (
                                classGroup.archived_at === null ? (
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        onClick={() => setArchiveOpen(true)}
                                    >
                                        <Archive className="size-4" aria-hidden="true" />
                                        Arsipkan
                                    </Button>
                                ) : (
                                    <Button
                                        type="button"
                                        size="sm"
                                        onClick={() => setRestoreOpen(true)}
                                    >
                                        <RotateCcw className="size-4" aria-hidden="true" />
                                        Pulihkan
                                    </Button>
                                )
                            ) : null}
                        </div>
                    </div>
                </div>

                <dl className="mt-5 grid gap-3 text-sm md:grid-cols-2">
                    <DetailField
                        label="Tahun ajaran"
                        value={referenceLabel(classGroup.academic_year)}
                    />
                    <DetailField
                        label="Semester"
                        value={referenceLabel(classGroup.academic_term)}
                    />
                    <DetailField
                        label="Kurikulum"
                        value={referenceLabel(classGroup.curriculum)}
                    />
                    <DetailField
                        label="Kapasitas"
                        value={String(classGroup.capacity ?? 'Belum diisi')}
                    />
                </dl>
            </section>

            <section className="grid gap-5 lg:grid-cols-[1.4fr_1fr]">
                <StudentList students={students} />
                <HomeroomList homerooms={homerooms} />
            </section>

            <StudentPlacementDialog
                open={placementOpen}
                classGroup={classGroup}
                students={options.students}
                onOpenChange={setPlacementOpen}
            />
            <HomeroomAssignmentDialog
                open={homeroomOpen}
                classGroup={classGroup}
                employees={options.employees}
                onOpenChange={setHomeroomOpen}
            />
            <ClassGroupArchiveDialog
                open={archiveOpen}
                classGroup={classGroup}
                mode="archive"
                onOpenChange={setArchiveOpen}
            />
            <ClassGroupArchiveDialog
                open={restoreOpen}
                classGroup={classGroup}
                mode="restore"
                onOpenChange={setRestoreOpen}
            />
        </div>
    );
}

function DetailField({ label, value }: { label: string; value: string }) {
    return (
        <div className="dashboard-subcard rounded-xl border p-3">
            <dt className="text-xs font-medium text-foreground/55">
                {label}
            </dt>
            <dd className="mt-1 font-medium">{value}</dd>
        </div>
    );
}

function StudentList({ students }: { students: ClassGroupStudent[] }) {
    return (
        <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <h2 className="font-semibold">Daftar santri</h2>
                    <p className="text-sm text-foreground/65">
                        Santri yang pernah ditempatkan pada rombel ini.
                    </p>
                </div>
                <UsersRound className="size-5 text-primary" aria-hidden="true" />
            </div>

            {students.length > 0 ? (
                <div className="mt-4 overflow-hidden rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                            <tr>
                                <th scope="col" className="px-4 py-3">
                                    Santri
                                </th>
                                <th scope="col" className="px-4 py-3">
                                    Masuk
                                </th>
                                <th scope="col" className="px-4 py-3">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {students.map((student) => (
                                <tr key={student.id}>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {student.student_name ??
                                                'Nama belum tersedia'}
                                        </div>
                                        <div className="text-xs text-foreground/60">
                                            {student.student_no}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-foreground/70">
                                        {student.joined_on}
                                    </td>
                                    <td className="px-4 py-3 text-foreground/70">
                                        {student.status}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : (
                <p className="mt-4 rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                    Belum ada santri pada rombel ini.
                </p>
            )}
        </section>
    );
}

function HomeroomList({ homerooms }: { homerooms: ClassGroupHomeroom[] }) {
    return (
        <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
            <h2 className="font-semibold">Riwayat wali kelas</h2>
            <p className="text-sm text-foreground/65">
                Wali kelas aktif dan riwayat penugasan pada rombel ini.
            </p>

            <div className="mt-4 space-y-3">
                {homerooms.length > 0 ? (
                    homerooms.map((homeroom) => (
                        <article
                            key={homeroom.id}
                            className="dashboard-subcard rounded-xl border p-3"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <h3 className="font-medium">
                                        {homeroom.employee_name}
                                    </h3>
                                    <p className="text-xs text-foreground/60">
                                        Sejak {homeroom.assigned_on}
                                    </p>
                                </div>
                                <span className="text-xs font-medium text-foreground/65">
                                    {homeroom.status}
                                </span>
                            </div>
                            {homeroom.reason ? (
                                <p className="mt-2 text-xs text-foreground/60">
                                    {homeroom.reason}
                                </p>
                            ) : null}
                        </article>
                    ))
                ) : (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                        Belum ada wali kelas pada rombel ini.
                    </p>
                )}
            </div>
        </section>
    );
}
