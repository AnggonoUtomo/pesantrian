import { Archive, PencilLine } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { OrganizationUnit } from '../types';
import { typeLabels } from './organizationUnitDisplay';
import { OrganizationUnitStatusBadge } from './OrganizationUnitStatusBadge';

type Props = {
    units: OrganizationUnit[];
    canManage: boolean;
    parentNameById: Map<string, string>;
    onEdit: (unit: OrganizationUnit) => void;
    onArchive: (unit: OrganizationUnit) => void;
};

export function OrganizationUnitList({
    units,
    canManage,
    parentNameById,
    onEdit,
    onArchive,
}: Props) {
    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                Unit
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Jenis
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Induk
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Status
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Lokasi
                            </th>
                            {canManage ? (
                                <th scope="col" className="px-4 py-3">
                                    Aksi
                                </th>
                            ) : null}
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {units.map((unit) => (
                            <tr key={unit.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {unit.name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {unit.code}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    {typeLabels[unit.type]}
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    <ParentLabel
                                        parentId={unit.parent_id}
                                        parentNameById={parentNameById}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <OrganizationUnitStatusBadge
                                        status={unit.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {unit.location_name ?? 'Belum diisi'}
                                </td>
                                {canManage ? (
                                    <td className="px-4 py-3">
                                        <OrganizationUnitRowActions
                                            unit={unit}
                                            editLabel="Edit"
                                            archiveLabel="Archive"
                                            onEdit={onEdit}
                                            onArchive={onArchive}
                                        />
                                    </td>
                                ) : null}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {units.map((unit) => (
                    <article
                        key={unit.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">{unit.name}</h3>
                                <p className="text-xs text-foreground/60">
                                    {unit.code} · {typeLabels[unit.type]}
                                </p>
                                <p className="text-xs text-foreground/60">
                                    Induk:{' '}
                                    <ParentLabel
                                        parentId={unit.parent_id}
                                        parentNameById={parentNameById}
                                    />
                                </p>
                            </div>
                            <OrganizationUnitStatusBadge
                                status={unit.status}
                            />
                        </div>
                        <p className="text-sm text-foreground/70">
                            {unit.location_name ?? 'Lokasi belum diisi'}
                        </p>
                        {canManage ? (
                            <OrganizationUnitRowActions
                                unit={unit}
                                editLabel="Edit unit"
                                archiveLabel="Archive unit"
                                onEdit={onEdit}
                                onArchive={onArchive}
                            />
                        ) : null}
                    </article>
                ))}
            </div>
        </div>
    );
}

function ParentLabel({
    parentId,
    parentNameById,
}: {
    parentId: string | null;
    parentNameById: Map<string, string>;
}) {
    if (!parentId) {
        return <>Tanpa induk</>;
    }

    return <>{parentNameById.get(parentId) ?? 'Induk tidak ditemukan'}</>;
}

function OrganizationUnitRowActions({
    unit,
    editLabel,
    archiveLabel,
    onEdit,
    onArchive,
}: {
    unit: OrganizationUnit;
    editLabel: string;
    archiveLabel: string;
    onEdit: (unit: OrganizationUnit) => void;
    onArchive: (unit: OrganizationUnit) => void;
}) {
    return (
        <div className="flex flex-wrap gap-2">
            <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => onEdit(unit)}
            >
                <PencilLine className="size-4" />
                {editLabel}
            </Button>
            {unit.status === 'active' ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onArchive(unit)}
                >
                    <Archive className="size-4" />
                    {archiveLabel}
                </Button>
            ) : null}
        </div>
    );
}
