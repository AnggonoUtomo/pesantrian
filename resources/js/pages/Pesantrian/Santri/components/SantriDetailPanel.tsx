import { Link } from '@inertiajs/react';
import { Archive, ArrowLeft, PencilLine, RefreshCcw } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { PrimaryUnitOption, Student } from '../types';
import { SantriArchiveDialog } from './SantriArchiveDialog';
import {
    genderLabels,
    primaryUnitLabel,
    primaryUnitNameMap,
} from './santriDisplay';
import { SantriLifecycleDialog } from './SantriLifecycleDialog';
import { SantriMutationDialog } from './SantriMutationDialog';
import { SantriStatusBadge } from './SantriStatusBadge';

type Props = {
    student: Student;
    primaryUnitOptions: PrimaryUnitOption[];
    canManage: boolean;
    canLifecycle: boolean;
    canArchive: boolean;
};

export function SantriDetailPanel({
    student,
    primaryUnitOptions,
    canManage,
    canLifecycle,
    canArchive,
}: Props) {
    const primaryUnitNameById = primaryUnitNameMap(primaryUnitOptions);
    const [mutationDialogOpen, setMutationDialogOpen] = useState(false);
    const [lifecycleDialogOpen, setLifecycleDialogOpen] = useState(false);
    const [archiveDialogOpen, setArchiveDialogOpen] = useState(false);

    return (
        <div className="space-y-5">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Button asChild variant="outline" size="sm">
                    <Link
                        href={routeOr(
                            '/pesantrian/students',
                            'pesantrian.students.index',
                        )}
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        Kembali ke daftar
                    </Link>
                </Button>
                <div className="flex flex-wrap gap-2">
                    {canManage ? (
                        <Button
                            type="button"
                            size="sm"
                            onClick={() => setMutationDialogOpen(true)}
                        >
                            <PencilLine className="size-4" aria-hidden="true" />
                            Edit data santri
                        </Button>
                    ) : null}
                    {canLifecycle ? (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => setLifecycleDialogOpen(true)}
                        >
                            <RefreshCcw
                                className="size-4"
                                aria-hidden="true"
                            />
                            Ubah status
                        </Button>
                    ) : null}
                    {canArchive ? (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => setArchiveDialogOpen(true)}
                        >
                            <Archive className="size-4" aria-hidden="true" />
                            Arsipkan
                        </Button>
                    ) : null}
                </div>
            </div>

            <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4 sm:p-5">
                <div className="flex flex-col gap-3 border-b pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-sm text-foreground/60">
                            Data induk santri
                        </p>
                        <h2 className="mt-1 text-xl font-semibold">
                            {student.full_name}
                        </h2>
                        <p className="text-sm text-foreground/65">
                            {student.student_no}
                        </p>
                    </div>
                    <SantriStatusBadge status={student.status} />
                </div>

                <div className="grid gap-5 pt-5 lg:grid-cols-3">
                    <DetailSection title="Identitas">
                        <DetailItem label="NIS" value={student.student_no} />
                        <DetailItem
                            label="Nomor PPDB"
                            value={student.registration_no}
                        />
                        <DetailItem
                            label="Nama panggilan"
                            value={student.preferred_name}
                        />
                        <DetailItem
                            label="Gender"
                            value={
                                student.gender
                                    ? genderLabels[student.gender]
                                    : null
                            }
                        />
                        <DetailItem
                            label="Tempat lahir"
                            value={student.birth_place}
                        />
                        <DetailItem
                            label="Tanggal lahir"
                            value={student.birth_date}
                        />
                        <DetailItem
                            label="Sekolah asal"
                            value={student.previous_school}
                        />
                    </DetailSection>

                    <DetailSection title="Wali snapshot">
                        <DetailItem
                            label="Nama wali"
                            value={student.primary_guardian?.guardian_name}
                        />
                        <DetailItem
                            label="Telepon wali"
                            value={student.primary_guardian?.guardian_phone}
                        />
                        <DetailItem
                            label="Relasi"
                            value={student.primary_guardian?.guardian_relation}
                        />
                        <DetailItem
                            label="Kontak darurat"
                            value={
                                student.primary_guardian
                                    ? student.primary_guardian
                                          .is_emergency_contact
                                        ? 'Ya'
                                        : 'Tidak'
                                    : null
                            }
                        />
                    </DetailSection>

                    <DetailSection title="Riwayat lifecycle">
                        <DetailItem
                            label="Unit utama"
                            value={primaryUnitLabel(
                                student.primary_unit_id,
                                primaryUnitNameById,
                            )}
                        />
                        <DetailItem
                            label="Tanggal masuk"
                            value={student.entry_date}
                        />
                        <DetailItem
                            label="Status"
                            value={student.status}
                        />
                        <DetailItem
                            label="Alasan status"
                            value={student.status_reason}
                        />
                        <DetailItem
                            label="Status berubah"
                            value={student.status_changed_at}
                        />
                    </DetailSection>
                </div>
            </section>
            <SantriMutationDialog
                open={mutationDialogOpen}
                student={student}
                primaryUnitOptions={primaryUnitOptions}
                onOpenChange={setMutationDialogOpen}
            />
            <SantriLifecycleDialog
                open={lifecycleDialogOpen}
                student={student}
                onOpenChange={setLifecycleDialogOpen}
            />
            <SantriArchiveDialog
                open={archiveDialogOpen}
                student={student}
                onOpenChange={setArchiveDialogOpen}
            />
        </div>
    );
}

function DetailSection({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="space-y-3">
            <h3 className="font-medium">{title}</h3>
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
