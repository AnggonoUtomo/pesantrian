import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useMemo, useState } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import route from '@/lib/route';
import { ArchiveOrganizationUnitDialog } from '../components/ArchiveOrganizationUnitDialog';
import { OrganizationUnitAccessDenied } from '../components/OrganizationUnitAccessDenied';
import { OrganizationUnitEmptyState } from '../components/OrganizationUnitEmptyState';
import { OrganizationUnitFilterForm } from '../components/OrganizationUnitFilterForm';
import { OrganizationUnitFormDialog } from '../components/OrganizationUnitFormDialog';
import { OrganizationUnitHeaderActions } from '../components/OrganizationUnitHeaderActions';
import { OrganizationUnitList } from '../components/OrganizationUnitList';
import { OrganizationUnitSummary } from '../components/OrganizationUnitSummary';
import type { OrganizationUnit, OrganizationUnitPageProps } from '../types';

export default function Index() {
    const { auth, units, parentOptions, filters, pagination, errors } =
        usePage<OrganizationUnitPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [type, setType] = useState<string>(filters.filter?.type ?? 'all');
    const [editingUnit, setEditingUnit] = useState<OrganizationUnit | null>(
        null,
    );
    const [archivingUnit, setArchivingUnit] =
        useState<OrganizationUnit | null>(null);
    const [formOpen, setFormOpen] = useState(false);

    const canView = canAccess(auth, 'organization.view');
    const canManage = canAccess(auth, 'organization.manage');
    const activeCount = useMemo(
        () => units.data.filter((unit) => unit.status === 'active').length,
        [units.data],
    );
    const parentNameById = useMemo(
        () =>
            new Map(
                parentOptions.map((parent) => [
                    parent.id,
                    `${parent.name} (${parent.code})`,
                ]),
            ),
        [parentOptions],
    );

    const openCreateDialog = () => {
        setEditingUnit(null);
        setFormOpen(true);
    };

    const openEditDialog = (unit: OrganizationUnit) => {
        setEditingUnit(unit);
        setFormOpen(true);
    };

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
                <OrganizationUnitAccessDenied />
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
                    <OrganizationUnitHeaderActions
                        total={units.meta.total}
                        canManage={canManage}
                        onCreate={openCreateDialog}
                    />
                }
            >
                <div className="space-y-5">
                    <OrganizationUnitSummary
                        total={units.meta.total}
                        active={activeCount}
                        inactive={units.data.length - activeCount}
                    />

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
                        <OrganizationUnitFilterForm
                            search={search}
                            status={status}
                            type={type}
                            perPage={units.meta.perPage}
                            onSearchChange={setSearch}
                            onStatusChange={setStatus}
                            onTypeChange={setType}
                            onSubmit={submitFilters}
                            onReset={resetFilters}
                        />

                        {units.data.length > 0 ? (
                            <OrganizationUnitList
                                units={units.data}
                                canManage={canManage}
                                parentNameById={parentNameById}
                                onEdit={openEditDialog}
                                onArchive={setArchivingUnit}
                            />
                        ) : (
                            <OrganizationUnitEmptyState
                                canManage={canManage}
                                onCreate={openCreateDialog}
                            />
                        )}
                    </section>
                </div>
            </SystemDashboardLayout>
            <OrganizationUnitFormDialog
                key={editingUnit?.id ?? 'new'}
                open={formOpen}
                unit={editingUnit}
                parentOptions={parentOptions}
                onOpenChange={(open) => {
                    setFormOpen(open);

                    if (!open) {
                        setEditingUnit(null);
                    }
                }}
            />
            <ArchiveOrganizationUnitDialog
                key={archivingUnit?.id ?? 'archive'}
                open={archivingUnit !== null}
                unit={archivingUnit}
                onOpenChange={(open) => {
                    if (!open) {
                        setArchivingUnit(null);
                    }
                }}
            />
        </>
    );
}
