import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { ClassGroup } from '../types';
import { referenceLabel } from './kelasRombelDisplay';
import { KelasRombelStatusBadge } from './KelasRombelStatusBadge';

type Props = {
    classGroups: ClassGroup[];
};

export function KelasRombelTable({ classGroups }: Props) {
    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                Rombel
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Periode
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Unit / Kelas
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Kurikulum
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Kapasitas
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Status
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Wali kelas
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {classGroups.map((classGroup) => (
                            <tr key={classGroup.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {classGroup.code}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {classGroup.name}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    <div>{classGroup.academic_year.name}</div>
                                    <div className="text-xs text-foreground/60">
                                        {classGroup.academic_term.name}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    <div>{classGroup.unit.name}</div>
                                    <div className="text-xs text-foreground/60">
                                        {classGroup.class_level.name}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {referenceLabel(classGroup.curriculum)}
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {classGroup.capacity ?? 'Belum diisi'}
                                </td>
                                <td className="px-4 py-3">
                                    <KelasRombelStatusBadge
                                        status={classGroup.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {activeHomeroomLabel(classGroup)}
                                </td>
                                <td className="px-4 py-3">
                                    <Button asChild variant="secondary" size="sm">
                                        <Link
                                            href={classGroupShowUrl(classGroup)}
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
                {classGroups.map((classGroup) => (
                    <article
                        key={classGroup.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {classGroup.code} · {classGroup.name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {classGroup.unit.name} ·{' '}
                                    {classGroup.class_level.name}
                                </p>
                            </div>
                            <KelasRombelStatusBadge
                                status={classGroup.status}
                            />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <ClassGroupField
                                label="Periode"
                                value={`${classGroup.academic_year.name} · ${classGroup.academic_term.name}`}
                            />
                            <ClassGroupField
                                label="Kurikulum"
                                value={referenceLabel(classGroup.curriculum)}
                            />
                            <ClassGroupField
                                label="Kapasitas"
                                value={String(
                                    classGroup.capacity ?? 'Belum diisi',
                                )}
                            />
                            <ClassGroupField
                                label="Wali kelas"
                                value={activeHomeroomLabel(classGroup)}
                            />
                        </dl>
                        <Button asChild variant="secondary" size="sm">
                            <Link href={classGroupShowUrl(classGroup)} prefetch>
                                Lihat detail
                            </Link>
                        </Button>
                    </article>
                ))}
            </div>
        </div>
    );
}

function ClassGroupField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-foreground/55">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

function activeHomeroomLabel(classGroup: ClassGroup): string {
    if (classGroup.homerooms === undefined) {
        return 'Lihat di detail';
    }

    const activeHomeroom = classGroup.homerooms?.find(
        (homeroom) => homeroom.status === 'active',
    );

    return activeHomeroom?.employee_name ?? 'Belum ada wali kelas';
}

function classGroupShowUrl(classGroup: ClassGroup): string {
    return String(
        routeOr(
            `/academic/class-groups/${classGroup.id}`,
            'academic.class-groups.show',
            classGroup.id,
        ),
    );
}
