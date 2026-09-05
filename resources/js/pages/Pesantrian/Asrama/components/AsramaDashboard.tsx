import { router, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import type { DormitoryArchiveFilter, DormitoryIndexPageProps } from '../types';
import { AsramaAccessDenied } from './AsramaAccessDenied';
import { AsramaEmptyState } from './AsramaEmptyState';
import { AsramaFilters } from './AsramaFilters';
import { DormitoryFormDialog } from './AsramaMutationDialogs';
import { AsramaPagination } from './AsramaPagination';
import { AsramaSummaryCards } from './AsramaSummaryCards';
import { AsramaTable } from './AsramaTable';

export function AsramaDashboard() {
    const {
        auth,
        dormitories,
        filters,
        pagination,
        options,
        errors,
        canManage,
    } = usePage<DormitoryIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [archived, setArchived] = useState<DormitoryArchiveFilter>(
        filters.filter?.archived ?? 'active',
    );
    const [genderPolicy, setGenderPolicy] = useState<string>(
        filters.filter?.gender_policy ?? 'all',
    );
    const [unitId, setUnitId] = useState<string>(
        filters.filter?.unit_id ?? 'all',
    );
    const [createOpen, setCreateOpen] = useState(false);
    const canView = canAccess(auth, 'asrama.view');
    const dormitoryIndexUrl = () =>
        routeOr('/pesantrian/asrama', 'pesantrian.asrama.index');

    const visitDormitories = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            dormitoryIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    status: status === 'all' ? undefined : status,
                    archived: archived === 'active' ? undefined : archived,
                    gender_policy:
                        genderPolicy === 'all' ? undefined : genderPolicy,
                    unit_id: unitId === 'all' ? undefined : unitId,
                },
                page: nextPage === 1 ? undefined : nextPage,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
                sort: filters.sort ?? '-created_at',
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    const submitFilters = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        visitDormitories();
    };

    const resetFilters = () => {
        setSearch('');
        setStatus('all');
        setArchived('active');
        setGenderPolicy('all');
        setUnitId('all');

        router.get(
            dormitoryIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <AsramaAccessDenied />;
    }

    return (
        <div className="space-y-5">
            <AsramaSummaryCards
                total={dormitories.meta.total}
                dormitories={dormitories.data}
            />
            {canManage ? (
                <div className="flex justify-end">
                    <Button type="button" onClick={() => setCreateOpen(true)}>
                        <Plus className="size-4" aria-hidden="true" />
                        Tambah asrama
                    </Button>
                </div>
            ) : null}

            {errors && Object.keys(errors).length > 0 ? (
                <p role="alert" className="dashboard-message--error text-sm">
                    Filter asrama tidak valid. Periksa input dan coba kembali.
                </p>
            ) : null}

            <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                <AsramaFilters
                    search={search}
                    status={status}
                    archived={archived}
                    genderPolicy={genderPolicy}
                    unitId={unitId}
                    perPage={dormitories.meta.perPage}
                    options={options}
                    onSearchChange={setSearch}
                    onStatusChange={setStatus}
                    onArchivedChange={setArchived}
                    onGenderPolicyChange={setGenderPolicy}
                    onUnitChange={setUnitId}
                    onSubmit={submitFilters}
                    onReset={resetFilters}
                />

                {dormitories.data.length > 0 ? (
                    <>
                        <AsramaTable dormitories={dormitories.data} />
                        <AsramaPagination
                            meta={dormitories.meta}
                            pagination={pagination}
                            onPageChange={(page) =>
                                visitDormitories(page, dormitories.meta.perPage)
                            }
                            onPerPageChange={(perPage) =>
                                visitDormitories(1, perPage)
                            }
                        />
                    </>
                ) : (
                    <AsramaEmptyState />
                )}
            </section>

            <DormitoryFormDialog
                open={createOpen}
                dormitory={null}
                units={options.units}
                onOpenChange={setCreateOpen}
            />
        </div>
    );
}
