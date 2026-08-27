import { Head, router, usePage } from '@inertiajs/react';
import {
    Building2,
    Filter,
    Network,
    PencilLine,
    Plus,
    ShieldCheck,
} from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import route from '@/lib/route';
import { OrganizationUnitFormDialog } from '../components/OrganizationUnitFormDialog';
import type {
    OrganizationUnit,
    OrganizationUnitFilters,
    OrganizationUnitPageProps,
    OrganizationUnitStatus,
    OrganizationUnitType,
} from '../types';

const statusLabels: Record<OrganizationUnitStatus, string> = {
    active: 'Aktif',
    inactive: 'Nonaktif',
};

const typeLabels: Record<OrganizationUnitType, string> = {
    foundation: 'Yayasan',
    pesantren: 'Pesantren',
    education_unit: 'Unit pendidikan',
    operational_unit: 'Unit operasional',
    dormitory: 'Asrama',
    other: 'Lainnya',
};

const typeOptions = Object.entries(typeLabels) as [
    OrganizationUnitType,
    string,
][];

export default function Index() {
    const { auth, units, filters, pagination, errors } =
        usePage<OrganizationUnitPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [type, setType] = useState<string>(filters.filter?.type ?? 'all');
    const [editingUnit, setEditingUnit] = useState<OrganizationUnit | null>(
        null,
    );
    const [formOpen, setFormOpen] = useState(false);
    const canView = canAccess(auth, 'organization.view');
    const canManage = canAccess(auth, 'organization.manage');
    const activeCount = useMemo(
        () => units.data.filter((unit) => unit.status === 'active').length,
        [units.data],
    );
    const inactiveCount = units.data.length - activeCount;

    const submitFilters = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        router.get(
            route('organization.units.index'),
            {
                search: search.trim() || undefined,
                filter: {
                    status: status === 'all' ? undefined : status,
                    type: type === 'all' ? undefined : type,
                },
                per_page: filters.per_page ?? pagination.defaultPerPage,
                sort: filters.sort ?? 'name',
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    const resetFilters = () => {
        setSearch('');
        setStatus('all');
        setType('all');

        router.get(
            route('organization.units.index'),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return (
            <>
                <Head title="Unit Organisasi" />
                <SystemDashboardLayout
                    eyebrow="Organization"
                    title="Unit Organisasi"
                    description="Kelola struktur yayasan dan unit operasional pesantren."
                >
                    <section className="dashboard-card dashboard-card--rose rounded-2xl border p-8 text-center">
                        <ShieldCheck className="mx-auto size-10 text-rose-500" />
                        <h2 className="mt-3 text-lg font-semibold">
                            Akses terbatas
                        </h2>
                        <p className="mt-2 text-sm text-foreground/65">
                            Akun ini tidak memiliki permission untuk melihat
                            unit organisasi.
                        </p>
                    </section>
                </SystemDashboardLayout>
            </>
        );
    }

    return (
        <>
            <Head title="Unit Organisasi" />
            <SystemDashboardLayout
                eyebrow="Organization"
                title="Unit Organisasi"
                description="Tinjau yayasan, pesantren, unit pendidikan, operasional, dan asrama sebagai data organisasi."
                actions={
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge
                            variant="secondary"
                            className="gap-2 rounded-full"
                        >
                            <Network className="size-3.5" aria-hidden="true" />
                            {units.meta.total} unit
                        </Badge>
                        {canManage ? (
                            <Button
                                type="button"
                                onClick={() => {
                                    setEditingUnit(null);
                                    setFormOpen(true);
                                }}
                            >
                                <Plus className="size-4" />
                                Tambah unit
                            </Button>
                        ) : null}
                    </div>
                }
            >
                <div className="space-y-5">
                    <section className="grid gap-3 sm:grid-cols-3">
                        <SummaryCard
                            title="Total unit"
                            value={units.meta.total}
                            description="Seluruh unit dalam scope filter."
                        />
                        <SummaryCard
                            title="Aktif di halaman ini"
                            value={activeCount}
                            description="Unit yang masih berjalan."
                        />
                        <SummaryCard
                            title="Nonaktif di halaman ini"
                            value={inactiveCount}
                            description="Unit yang diarsipkan dari operasional."
                        />
                    </section>

                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Filter unit organisasi tidak valid. Periksa input
                            dan coba kembali.
                        </p>
                    ) : null}

                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h2 className="font-semibold">
                                    Daftar unit
                                </h2>
                                <p className="text-sm text-foreground/65">
                                    Gunakan pencarian dan filter untuk
                                    menemukan unit organisasi yang dibutuhkan.
                                </p>
                            </div>
                            <Badge variant="outline" className="gap-2">
                                <Filter className="size-3.5" />
                                {units.meta.perPage} per halaman
                            </Badge>
                        </div>

                        <form
                            className="grid gap-3 lg:grid-cols-[1fr_180px_220px_auto]"
                            onSubmit={submitFilters}
                        >
                            <label className="space-y-1.5">
                                <span className="text-xs font-medium text-foreground/70">
                                    Cari unit
                                </span>
                                <Input
                                    id="organization-unit-search"
                                    type="search"
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Cari nama atau kode unit"
                                />
                            </label>
                            <label className="space-y-1.5">
                                <span className="text-xs font-medium text-foreground/70">
                                    Status
                                </span>
                                <Select
                                    value={status}
                                    onValueChange={setStatus}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            Semua status
                                        </SelectItem>
                                        <SelectItem value="active">
                                            Aktif
                                        </SelectItem>
                                        <SelectItem value="inactive">
                                            Nonaktif
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </label>
                            <label className="space-y-1.5">
                                <span className="text-xs font-medium text-foreground/70">
                                    Jenis unit
                                </span>
                                <Select value={type} onValueChange={setType}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua jenis" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            Semua jenis
                                        </SelectItem>
                                        {typeOptions.map(([value, label]) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </label>
                            <div className="flex items-end gap-2">
                                <Button type="submit">Terapkan</Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={resetFilters}
                                >
                                    Reset
                                </Button>
                            </div>
                        </form>

                        {units.data.length > 0 ? (
                            <OrganizationUnitList
                                units={units.data}
                                canManage={canManage}
                                onEdit={(unit) => {
                                    setEditingUnit(unit);
                                    setFormOpen(true);
                                }}
                            />
                        ) : (
                            <EmptyState
                                canManage={canManage}
                                onCreate={() => {
                                    setEditingUnit(null);
                                    setFormOpen(true);
                                }}
                            />
                        )}
                    </section>
                </div>
            </SystemDashboardLayout>
            <OrganizationUnitFormDialog
                key={editingUnit?.id ?? 'new'}
                open={formOpen}
                unit={editingUnit}
                onOpenChange={(open) => {
                    setFormOpen(open);

                    if (!open) {
                        setEditingUnit(null);
                    }
                }}
            />
        </>
    );
}

function SummaryCard({
    title,
    value,
    description,
}: {
    title: string;
    value: number;
    description: string;
}) {
    return (
        <article className="dashboard-card rounded-2xl border p-4">
            <p className="text-sm text-foreground/65">{title}</p>
            <p className="mt-2 text-2xl font-semibold">{value}</p>
            <p className="mt-1 text-xs text-foreground/60">{description}</p>
        </article>
    );
}

function OrganizationUnitList({
    units,
    canManage,
    onEdit,
}: {
    units: OrganizationUnit[];
    canManage: boolean;
    onEdit: (unit: OrganizationUnit) => void;
}) {
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
                                <td className="px-4 py-3">
                                    <StatusBadge status={unit.status} />
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {unit.location_name ?? 'Belum diisi'}
                                </td>
                                {canManage ? (
                                    <td className="px-4 py-3">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => onEdit(unit)}
                                        >
                                            <PencilLine className="size-4" />
                                            Edit
                                        </Button>
                                    </td>
                                ) : null}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {units.map((unit) => (
                    <article key={unit.id} className="space-y-3 bg-background p-4">
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">{unit.name}</h3>
                                <p className="text-xs text-foreground/60">
                                    {unit.code} · {typeLabels[unit.type]}
                                </p>
                            </div>
                            <StatusBadge status={unit.status} />
                        </div>
                        <p className="text-sm text-foreground/70">
                            {unit.location_name ?? 'Lokasi belum diisi'}
                        </p>
                        {canManage ? (
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => onEdit(unit)}
                            >
                                <PencilLine className="size-4" />
                                Edit unit
                            </Button>
                        ) : null}
                    </article>
                ))}
            </div>
        </div>
    );
}

function StatusBadge({ status }: { status: OrganizationUnitStatus }) {
    return (
        <Badge variant={status === 'active' ? 'default' : 'secondary'}>
            {statusLabels[status]}
        </Badge>
    );
}

function EmptyState({
    canManage,
    onCreate,
}: {
    canManage: boolean;
    onCreate: () => void;
}) {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <Building2 className="mx-auto size-10 text-foreground/45" />
            <h3 className="mt-3 font-semibold">Belum ada unit ditemukan</h3>
            <p className="mx-auto mt-2 max-w-md text-sm text-foreground/65">
                Ubah filter pencarian atau mulai isi data unit organisasi
                melalui endpoint backend yang sudah tersedia.
            </p>
            {canManage ? (
                <Button type="button" className="mt-4" onClick={onCreate}>
                    <Plus className="size-4" />
                    Tambah unit organisasi
                </Button>
            ) : null}
        </div>
    );
}
