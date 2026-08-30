import { Button } from '@/components/ui/button';
import type { AdmissionTargetUnitOption, StudentAdmission } from '../types';
import { genderLabels, targetUnitLabel } from './admissionDisplay';
import { AdmissionStatusBadge } from './AdmissionStatusBadge';
import { RegistrationFeeStatusBadge } from './RegistrationFeeStatusBadge';

type Props = {
    admissions: StudentAdmission[];
    targetUnitNameById: Map<string, string>;
    canManage: boolean;
    onEdit: (admission: StudentAdmission) => void;
};

export function AdmissionList({
    admissions,
    targetUnitNameById,
    canManage,
    onEdit,
}: Props) {
    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                Nomor pendaftaran
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Calon santri
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Unit tujuan
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Wali
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Biaya
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Status
                            </th>
                            {canManage ? (
                                <th scope="col" className="px-4 py-3">
                                    Aksi
                                </th>
                            ) : null}
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {admissions.map((admission) => (
                            <tr key={admission.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {admission.registration_no}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {admission.registration_period ??
                                            'Periode belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {admission.candidate_name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {admission.candidate_gender
                                            ? genderLabels[
                                                  admission.candidate_gender
                                              ]
                                            : 'Gender belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {targetUnitLabel(
                                        admission.target_unit_id,
                                        targetUnitNameById,
                                    )}
                                </td>
                                <td className="px-4 py-3">
                                    <div>{admission.guardian_name}</div>
                                    <div className="text-xs text-foreground/60">
                                        {admission.guardian_relation ??
                                            'Relasi belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <RegistrationFeeStatusBadge
                                        status={
                                            admission.registration_fee_status
                                        }
                                    />
                                    <div className="mt-1 text-xs text-foreground/60">
                                        {admission.registration_fee_amount ??
                                            'Tidak ada nominal'}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <AdmissionStatusBadge
                                        status={admission.status}
                                    />
                                </td>
                                {canManage ? (
                                    <td className="px-4 py-3">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => onEdit(admission)}
                                            disabled={!canEdit(admission)}
                                        >
                                            Edit pendaftaran
                                        </Button>
                                    </td>
                                ) : null}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {admissions.map((admission) => (
                    <article
                        key={admission.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {admission.candidate_name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {admission.registration_no} ·{' '}
                                    {admission.registration_period ??
                                        'Periode belum diisi'}
                                </p>
                            </div>
                            <AdmissionStatusBadge status={admission.status} />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <AdmissionField
                                label="Unit"
                                value={targetUnitLabel(
                                    admission.target_unit_id,
                                    targetUnitNameById,
                                )}
                            />
                            <AdmissionField
                                label="Wali"
                                value={admission.guardian_name}
                            />
                            <AdmissionField
                                label="Biaya"
                                value={
                                    admission.registration_fee_amount ??
                                    'Tidak ada nominal'
                                }
                            />
                        </dl>
                        {canManage ? (
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => onEdit(admission)}
                                disabled={!canEdit(admission)}
                            >
                                Edit pendaftaran
                            </Button>
                        ) : null}
                    </article>
                ))}
            </div>
        </div>
    );
}

function canEdit(admission: StudentAdmission): boolean {
    return ['draft', 'submitted', 'verified'].includes(admission.status);
}

function AdmissionField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-foreground/55">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

export function targetUnitNameMap(
    options: AdmissionTargetUnitOption[],
): Map<string, string> {
    return new Map(
        options.map((unit) => [unit.id, `${unit.name} (${unit.code})`]),
    );
}
