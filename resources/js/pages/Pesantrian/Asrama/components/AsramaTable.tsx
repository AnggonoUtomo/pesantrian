import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { Dormitory } from '../types';
import { genderPolicyLabel, occupancyLabel } from './asramaDisplay';
import { AsramaStatusBadge } from './AsramaStatusBadge';

type Props = {
    dormitories: Dormitory[];
};

export function AsramaTable({ dormitories }: Props) {
    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                Asrama
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Unit
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Tipe penghuni
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Kamar
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Hunian
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Status
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {dormitories.map((dormitory) => (
                            <tr key={dormitory.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {dormitory.code}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {dormitory.name}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {dormitory.unit.name}
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {genderPolicyLabel(dormitory.gender_policy)}
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {dormitory.room_count} kamar
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {occupancyLabel(
                                        dormitory.occupied_count,
                                        dormitory.capacity,
                                    )}
                                </td>
                                <td className="px-4 py-3">
                                    <AsramaStatusBadge
                                        status={dormitory.status}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <Button
                                        asChild
                                        variant="secondary"
                                        size="sm"
                                    >
                                        <Link
                                            href={dormitoryShowUrl(dormitory)}
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
                {dormitories.map((dormitory) => (
                    <article
                        key={dormitory.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {dormitory.code} · {dormitory.name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {dormitory.unit.name} ·{' '}
                                    {genderPolicyLabel(dormitory.gender_policy)}
                                </p>
                            </div>
                            <AsramaStatusBadge status={dormitory.status} />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <DormitoryField
                                label="Kamar"
                                value={`${dormitory.room_count} kamar`}
                            />
                            <DormitoryField
                                label="Hunian"
                                value={occupancyLabel(
                                    dormitory.occupied_count,
                                    dormitory.capacity,
                                )}
                            />
                            <DormitoryField
                                label="Sisa kapasitas"
                                value={String(dormitory.available_capacity)}
                            />
                        </dl>
                        <Button asChild variant="secondary" size="sm">
                            <Link href={dormitoryShowUrl(dormitory)} prefetch>
                                Lihat detail
                            </Link>
                        </Button>
                    </article>
                ))}
            </div>
        </div>
    );
}

function DormitoryField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-foreground/55">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

function dormitoryShowUrl(dormitory: Dormitory): string {
    return String(
        routeOr(
            `/pesantrian/asrama/${dormitory.id}`,
            'pesantrian.asrama.show',
            dormitory.id,
        ),
    );
}
