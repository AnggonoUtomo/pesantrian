import { FileText, UserRound, UsersRound } from 'lucide-react';
import type { ReactNode } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type {
    AdmissionChecklistItem,
    AdmissionChecklistStatus,
    StudentAdmission,
} from '../types';
import {
    genderLabels,
    registrationFeeStatusLabels,
    targetUnitLabel,
} from './admissionDisplay';
import { AdmissionStatusBadge } from './AdmissionStatusBadge';
import { RegistrationFeeStatusBadge } from './RegistrationFeeStatusBadge';

type Props = {
    open: boolean;
    admission: StudentAdmission | null;
    targetUnitNameById: Map<string, string>;
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

export function AdmissionDetailDialog({
    open,
    admission,
    targetUnitNameById,
    onOpenChange,
}: Props) {
    if (admission === null) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] max-w-4xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <FileText className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>Detail pendaftaran</DialogTitle>
                            <DialogDescription className="mt-1">
                                {admission.registration_no} ·{' '}
                                {admission.candidate_name}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div className="space-y-4">
                    <section className="rounded-xl border p-4">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 className="text-base font-semibold">
                                    Ringkasan status
                                </h3>
                                <p className="text-sm text-foreground/60">
                                    Status lifecycle dan administrasi utama
                                    pendaftaran.
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <AdmissionStatusBadge
                                    status={admission.status}
                                />
                                <RegistrationFeeStatusBadge
                                    status={admission.registration_fee_status}
                                />
                            </div>
                        </div>
                        <DetailGrid className="mt-4">
                            <DetailItem
                                label="Nomor pendaftaran"
                                value={admission.registration_no}
                            />
                            <DetailItem
                                label="Periode"
                                value={admission.registration_period}
                            />
                            <DetailItem
                                label="Tanggal daftar"
                                value={formatDate(admission.registered_at)}
                            />
                            <DetailItem
                                label="Update terakhir"
                                value={formatDate(admission.updated_at)}
                            />
                        </DetailGrid>
                    </section>

                    <div className="grid gap-4 lg:grid-cols-2">
                        <DetailSection
                            icon={<UserRound className="size-5" />}
                            title="Data calon santri"
                        >
                            <DetailGrid>
                                <DetailItem
                                    label="Nama"
                                    value={admission.candidate_name}
                                />
                                <DetailItem
                                    label="Jenis kelamin"
                                    value={
                                        admission.candidate_gender
                                            ? genderLabels[
                                                  admission.candidate_gender
                                              ]
                                            : null
                                    }
                                />
                                <DetailItem
                                    label="Tempat lahir"
                                    value={admission.candidate_birth_place}
                                />
                                <DetailItem
                                    label="Tanggal lahir"
                                    value={formatDate(
                                        admission.candidate_birth_date,
                                    )}
                                />
                                <DetailItem
                                    label="Sekolah asal"
                                    value={admission.previous_school}
                                />
                                <DetailItem
                                    label="Unit tujuan"
                                    value={targetUnitLabel(
                                        admission.target_unit_id,
                                        targetUnitNameById,
                                    )}
                                />
                            </DetailGrid>
                        </DetailSection>

                        <DetailSection
                            icon={<UsersRound className="size-5" />}
                            title="Data wali"
                        >
                            <DetailGrid>
                                <DetailItem
                                    label="Nama wali"
                                    value={admission.guardian_name}
                                />
                                <DetailItem
                                    label="No. HP"
                                    value={admission.guardian_phone}
                                />
                                <DetailItem
                                    label="Hubungan"
                                    value={admission.guardian_relation}
                                />
                            </DetailGrid>
                        </DetailSection>
                    </div>

                    <DetailSection title="Administrasi biaya">
                        <DetailGrid>
                            <DetailItem
                                label="Wajib biaya"
                                value={
                                    admission.registration_fee_required
                                        ? 'Ya'
                                        : 'Tidak'
                                }
                            />
                            <DetailItem
                                label="Nominal"
                                value={admission.registration_fee_amount}
                            />
                            <DetailItem
                                label="Status biaya"
                                value={
                                    registrationFeeStatusLabels[
                                        admission.registration_fee_status
                                    ]
                                }
                            />
                        </DetailGrid>
                    </DetailSection>

                    <DetailSection title="Checklist dokumen">
                        <ChecklistItems
                            items={admission.document_checklist ?? []}
                        />
                    </DetailSection>

                    <DetailSection title="Riwayat keputusan">
                        <DetailGrid>
                            <DetailItem
                                label="Tanggal keputusan"
                                value={formatDate(admission.decided_at)}
                            />
                            <DetailItem
                                label="Diputuskan oleh"
                                value={admission.decided_by}
                            />
                            <DetailItem
                                label="Catatan"
                                value={admission.notes}
                            />
                        </DetailGrid>
                    </DetailSection>
                </div>
            </DialogContent>
        </Dialog>
    );
}

function DetailSection({
    icon,
    title,
    children,
}: {
    icon?: ReactNode;
    title: string;
    children: ReactNode;
}) {
    return (
        <section className="rounded-xl border p-4">
            <div className="mb-4 flex items-center gap-2">
                {icon ? (
                    <span className="text-primary" aria-hidden="true">
                        {icon}
                    </span>
                ) : null}
                <h3 className="text-base font-semibold">{title}</h3>
            </div>
            {children}
        </section>
    );
}

function DetailGrid({
    className,
    children,
}: {
    className?: string;
    children: ReactNode;
}) {
    return (
        <dl
            className={[
                'grid gap-3 text-sm sm:grid-cols-2',
                className ?? '',
            ].join(' ')}
        >
            {children}
        </dl>
    );
}

function DetailItem({
    label,
    value,
}: {
    label: string;
    value: string | number | boolean | null;
}) {
    return (
        <div>
            <dt className="text-xs text-foreground/55">{label}</dt>
            <dd className="mt-1 font-medium text-foreground">
                {formatValue(value)}
            </dd>
        </div>
    );
}

function ChecklistItems({ items }: { items: AdmissionChecklistItem[] }) {
    if (items.length === 0) {
        return (
            <p className="text-sm text-foreground/60">
                Belum ada checklist dokumen yang dicatat.
            </p>
        );
    }

    return (
        <div className="grid gap-3">
            {items.map((item) => (
                <div
                    key={item.type}
                    className="rounded-lg border bg-muted/20 p-3"
                >
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <div className="font-medium">
                            {checklistLabels[item.type] ?? item.type}
                        </div>
                        <span className="rounded-full bg-secondary px-2.5 py-1 text-xs font-medium text-secondary-foreground">
                            {checklistStatusLabels[item.status]}
                        </span>
                    </div>
                    {item.notes ? (
                        <p className="mt-2 text-sm text-foreground/65">
                            {item.notes}
                        </p>
                    ) : null}
                </div>
            ))}
        </div>
    );
}

function formatDate(value: string | null): string | null {
    if (!value) {
        return null;
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: value.includes(':') ? 'short' : undefined,
    }).format(new Date(value));
}

function formatValue(value: string | number | boolean | null): string {
    if (value === null || value === '') {
        return 'Belum diisi';
    }

    if (typeof value === 'boolean') {
        return value ? 'Ya' : 'Tidak';
    }

    return String(value);
}
