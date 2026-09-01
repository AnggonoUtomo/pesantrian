import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { PrimaryUnitOption, Student } from '../types';
import {
    genderLabels,
    primaryUnitLabel,
    primaryUnitNameMap,
} from './santriDisplay';
import { SantriStatusBadge } from './SantriStatusBadge';

type Props = {
    students: Student[];
    primaryUnitOptions: PrimaryUnitOption[];
};

export function SantriTable({ students, primaryUnitOptions }: Props) {
    const primaryUnitNameById = primaryUnitNameMap(primaryUnitOptions);

    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                NIS
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Santri
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Unit utama
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Wali utama
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Status
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Detail
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {students.map((student) => (
                            <tr key={student.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {student.student_no}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {student.registration_no ??
                                            'Tanpa asal PPDB'}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {student.full_name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {student.gender
                                            ? genderLabels[student.gender]
                                            : 'Gender belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {primaryUnitLabel(
                                        student.primary_unit_id,
                                        primaryUnitNameById,
                                    )}
                                </td>
                                <td className="px-4 py-3">
                                    <div>
                                        {student.primary_guardian
                                            ?.guardian_name ??
                                            'Wali belum diisi'}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {student.primary_guardian
                                            ?.guardian_relation ??
                                            'Relasi belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <SantriStatusBadge
                                        status={student.status}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <Button asChild variant="secondary" size="sm">
                                        <Link
                                            href={studentShowUrl(student)}
                                            prefetch
                                        >
                                            Lihat detail
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {students.map((student) => (
                    <article
                        key={student.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {student.full_name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {student.student_no} ·{' '}
                                    {student.registration_no ??
                                        'Tanpa asal PPDB'}
                                </p>
                            </div>
                            <SantriStatusBadge status={student.status} />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <StudentField
                                label="Unit"
                                value={primaryUnitLabel(
                                    student.primary_unit_id,
                                    primaryUnitNameById,
                                )}
                            />
                            <StudentField
                                label="Wali utama"
                                value={
                                    student.primary_guardian?.guardian_name ??
                                    'Wali belum diisi'
                                }
                            />
                            <StudentField
                                label="Tanggal masuk"
                                value={student.entry_date ?? 'Belum diisi'}
                            />
                        </dl>
                        <Button asChild variant="secondary" size="sm">
                            <Link href={studentShowUrl(student)} prefetch>
                                Lihat detail
                            </Link>
                        </Button>
                    </article>
                ))}
            </div>
        </div>
    );
}

function StudentField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-foreground/55">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

function studentShowUrl(student: Student): string {
    return String(
        routeOr(
            `/pesantrian/students/${student.id}`,
            'pesantrian.students.show',
            student.id,
        ),
    );
}
