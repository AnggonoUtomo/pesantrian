import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import type { TahfidzIndexPageProps } from '../types';
import { TahfidzAccessDenied } from './TahfidzAccessDenied';
import { TahfidzActionBar } from './TahfidzActionBar';
import { TahfidzEmptyState } from './TahfidzEmptyState';
import { TahfidzFilters } from './TahfidzFilters';
import { TahfidzPagination } from './TahfidzPagination';
import { TahfidzProgramDialog } from './TahfidzProgramDialog';
import { TahfidzSubmissionDialog } from './TahfidzSubmissionDialog';
import { TahfidzSummaryCards } from './TahfidzSummaryCards';
import { TahfidzTable } from './TahfidzTable';
import { TahfidzTargetDialog } from './TahfidzTargetDialog';

export function TahfidzDashboard() {
    const {
        auth,
        submissions,
        filters,
        pagination,
        options,
        errors,
        canManage,
        canRecord,
    } = usePage<TahfidzIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [dateFrom, setDateFrom] = useState(filters.filter?.date_from ?? '');
    const [dateTo, setDateTo] = useState(filters.filter?.date_to ?? '');
    const [type, setType] = useState<string>(filters.filter?.type ?? 'all');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [programOpen, setProgramOpen] = useState(false);
    const [targetOpen, setTargetOpen] = useState(false);
    const [submissionOpen, setSubmissionOpen] = useState(false);
    const canView = canAccess(auth, 'tahfidz.view');
    const tahfidzIndexUrl = () =>
        routeOr('/pesantrian/tahfidz', 'pesantrian.tahfidz.index');

    const visitSubmissions = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            tahfidzIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    date_from: dateFrom || undefined,
                    date_to: dateTo || undefined,
                    type: type === 'all' ? undefined : type,
                    status: status === 'all' ? undefined : status,
                },
                page: nextPage === 1 ? undefined : nextPage,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
                sort: filters.sort ?? '-submission_date',
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
        visitSubmissions();
    };

    const resetFilters = () => {
        setSearch('');
        setDateFrom('');
        setDateTo('');
        setType('all');
        setStatus('all');

        router.get(
            tahfidzIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <TahfidzAccessDenied />;
    }

    return (
        <div className="space-y-5">
            <TahfidzSummaryCards
                total={submissions.meta.total}
                submissions={submissions.data}
            />

            <TahfidzActionBar
                canManage={canManage}
                canRecord={canRecord}
                onCreateProgram={() => setProgramOpen(true)}
                onCreateTarget={() => setTargetOpen(true)}
                onCreateSubmission={() => setSubmissionOpen(true)}
            />

            {errors && Object.keys(errors).length > 0 ? (
                <p role="alert" className="dashboard-message--error text-sm">
                    Filter Tahfidz / Hafalan tidak valid. Periksa input dan
                    coba kembali.
                </p>
            ) : null}

            <section className="dashboard-card dashboard-card--green space-y-4 rounded-2xl border p-4 sm:p-5">
                <TahfidzFilters
                    search={search}
                    dateFrom={dateFrom}
                    dateTo={dateTo}
                    type={type}
                    status={status}
                    perPage={submissions.meta.perPage}
                    options={options}
                    onSearchChange={setSearch}
                    onDateFromChange={setDateFrom}
                    onDateToChange={setDateTo}
                    onTypeChange={setType}
                    onStatusChange={setStatus}
                    onSubmit={submitFilters}
                    onReset={resetFilters}
                />

                {submissions.data.length > 0 ? (
                    <>
                        <TahfidzTable submissions={submissions.data} />
                        <TahfidzPagination
                            meta={submissions.meta}
                            pagination={pagination}
                            onPageChange={(page) =>
                                visitSubmissions(page, submissions.meta.perPage)
                            }
                            onPerPageChange={(perPage) =>
                                visitSubmissions(1, perPage)
                            }
                        />
                    </>
                ) : (
                    <TahfidzEmptyState />
                )}
            </section>

            <TahfidzProgramDialog
                open={programOpen}
                options={options}
                onOpenChange={setProgramOpen}
            />
            <TahfidzTargetDialog
                open={targetOpen}
                options={options}
                onOpenChange={setTargetOpen}
            />
            <TahfidzSubmissionDialog
                open={submissionOpen}
                submission={null}
                options={options}
                onOpenChange={setSubmissionOpen}
            />
        </div>
    );
}
