import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import type { StudentDisciplineIndexPageProps } from '../types';
import { KedisiplinanSantriAccessDenied } from './KedisiplinanSantriAccessDenied';
import { KedisiplinanSantriEmptyState } from './KedisiplinanSantriEmptyState';
import { KedisiplinanSantriFilters } from './KedisiplinanSantriFilters';
import { KedisiplinanSantriPagination } from './KedisiplinanSantriPagination';
import { KedisiplinanSantriSummaryCards } from './KedisiplinanSantriSummaryCards';
import { KedisiplinanSantriTable } from './KedisiplinanSantriTable';

export function KedisiplinanSantriDashboard() {
    const { auth, cases, filters, pagination, options, errors } =
        usePage<StudentDisciplineIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [dateFrom, setDateFrom] = useState(
        filters.filter?.date_from ?? '',
    );
    const [dateTo, setDateTo] = useState(filters.filter?.date_to ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [severity, setSeverity] = useState<string>(
        filters.filter?.severity ?? 'all',
    );
    const [categoryId, setCategoryId] = useState<string>(
        filters.filter?.category_id ?? 'all',
    );
    const canView = canAccess(auth, 'kedisiplinan_santri.view');
    const disciplineIndexUrl = () =>
        routeOr(
            '/pesantrian/student-discipline-cases',
            'pesantrian.student-discipline-cases.index',
        );

    const visitCases = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            disciplineIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    date_from: dateFrom || undefined,
                    date_to: dateTo || undefined,
                    status: status === 'all' ? undefined : status,
                    severity: severity === 'all' ? undefined : severity,
                    category_id:
                        categoryId === 'all' ? undefined : categoryId,
                },
                page: nextPage === 1 ? undefined : nextPage,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
                sort: filters.sort ?? '-occurred_at',
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
        visitCases();
    };

    const resetFilters = () => {
        setSearch('');
        setDateFrom('');
        setDateTo('');
        setStatus('all');
        setSeverity('all');
        setCategoryId('all');

        router.get(
            disciplineIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <KedisiplinanSantriAccessDenied />;
    }

    return (
        <div className="space-y-5">
            <KedisiplinanSantriSummaryCards
                total={cases.meta.total}
                cases={cases.data}
            />

            {errors && Object.keys(errors).length > 0 ? (
                <p role="alert" className="dashboard-message--error text-sm">
                    Filter kedisiplinan santri tidak valid. Periksa input dan
                    coba kembali.
                </p>
            ) : null}

            <section className="dashboard-card dashboard-card--amber space-y-4 rounded-2xl border p-4 sm:p-5">
                <KedisiplinanSantriFilters
                    search={search}
                    dateFrom={dateFrom}
                    dateTo={dateTo}
                    status={status}
                    severity={severity}
                    categoryId={categoryId}
                    perPage={cases.meta.perPage}
                    options={options}
                    onSearchChange={setSearch}
                    onDateFromChange={setDateFrom}
                    onDateToChange={setDateTo}
                    onStatusChange={setStatus}
                    onSeverityChange={setSeverity}
                    onCategoryChange={setCategoryId}
                    onSubmit={submitFilters}
                    onReset={resetFilters}
                />

                {cases.data.length > 0 ? (
                    <>
                        <KedisiplinanSantriTable cases={cases.data} />
                        <KedisiplinanSantriPagination
                            meta={cases.meta}
                            pagination={pagination}
                            onPageChange={(page) =>
                                visitCases(page, cases.meta.perPage)
                            }
                            onPerPageChange={(perPage) =>
                                visitCases(1, perPage)
                            }
                        />
                    </>
                ) : (
                    <KedisiplinanSantriEmptyState />
                )}
            </section>
        </div>
    );
}
