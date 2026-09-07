import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import { PerizinanSantriAccessDenied } from '../components/PerizinanSantriAccessDenied';
import { PerizinanSantriEmptyState } from '../components/PerizinanSantriEmptyState';
import { PerizinanSantriFilters } from '../components/PerizinanSantriFilters';
import { PerizinanSantriPagination } from '../components/PerizinanSantriPagination';
import { PerizinanSantriSummaryCards } from '../components/PerizinanSantriSummaryCards';
import { PerizinanSantriTable } from '../components/PerizinanSantriTable';
import type { StudentPermitIndexPageProps } from '../types';

export default function Index() {
    const { auth, permits, filters, pagination, options, errors } =
        usePage<StudentPermitIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [dateFrom, setDateFrom] = useState(
        filters.filter?.date_from ?? '',
    );
    const [dateTo, setDateTo] = useState(filters.filter?.date_to ?? '');
    const [permitType, setPermitType] = useState<string>(
        filters.filter?.permit_type ?? 'all',
    );
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [isLate, setIsLate] = useState<string>(
        filters.filter?.is_late === undefined
            ? 'all'
            : String(filters.filter.is_late),
    );
    const canView = canAccess(auth, 'perizinan_santri.view');
    const permitIndexUrl = () =>
        routeOr(
            '/pesantrian/student-permits',
            'pesantrian.student-permits.index',
        );

    const visitPermits = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            permitIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    date_from: dateFrom || undefined,
                    date_to: dateTo || undefined,
                    permit_type:
                        permitType === 'all' ? undefined : permitType,
                    status: status === 'all' ? undefined : status,
                    is_late: isLate === 'all' ? undefined : isLate,
                },
                page: nextPage === 1 ? undefined : nextPage,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
                sort: filters.sort ?? '-starts_at',
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
        visitPermits();
    };

    const resetFilters = () => {
        setSearch('');
        setDateFrom('');
        setDateTo('');
        setPermitType('all');
        setStatus('all');
        setIsLate('all');

        router.get(
            permitIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <PerizinanSantriAccessDenied />;
    }

    return (
        <>
            <Head title="Perizinan Santri" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Perizinan Santri"
                description="Pantau permohonan izin, approval, check-out, return/check-in, void, dan keterlambatan santri."
            >
                <div className="space-y-5">
                    <PerizinanSantriSummaryCards
                        total={permits.meta.total}
                        permits={permits.data}
                    />

                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Filter perizinan santri tidak valid. Periksa input
                            dan coba kembali.
                        </p>
                    ) : null}

                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <PerizinanSantriFilters
                            search={search}
                            dateFrom={dateFrom}
                            dateTo={dateTo}
                            permitType={permitType}
                            status={status}
                            isLate={isLate}
                            perPage={permits.meta.perPage}
                            options={options}
                            onSearchChange={setSearch}
                            onDateFromChange={setDateFrom}
                            onDateToChange={setDateTo}
                            onPermitTypeChange={setPermitType}
                            onStatusChange={setStatus}
                            onIsLateChange={setIsLate}
                            onSubmit={submitFilters}
                            onReset={resetFilters}
                        />

                        {permits.data.length > 0 ? (
                            <>
                                <PerizinanSantriTable
                                    permits={permits.data}
                                />
                                <PerizinanSantriPagination
                                    meta={permits.meta}
                                    pagination={pagination}
                                    onPageChange={(page) =>
                                        visitPermits(page, permits.meta.perPage)
                                    }
                                    onPerPageChange={(perPage) =>
                                        visitPermits(1, perPage)
                                    }
                                />
                            </>
                        ) : (
                            <PerizinanSantriEmptyState />
                        )}
                    </section>
                </div>
            </SystemDashboardLayout>
        </>
    );
}
