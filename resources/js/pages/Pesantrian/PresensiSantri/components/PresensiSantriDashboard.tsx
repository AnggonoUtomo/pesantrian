import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import type { StudentAttendance } from '../types';
import type { StudentAttendanceIndexPageProps } from '../types';
import { PresensiSantriAccessDenied } from './PresensiSantriAccessDenied';
import { PresensiSantriActionBar } from './PresensiSantriActionBar';
import { PresensiSantriEmptyState } from './PresensiSantriEmptyState';
import { PresensiSantriFilters } from './PresensiSantriFilters';
import { PresensiSantriMutationDialog } from './PresensiSantriMutationDialog';
import { PresensiSantriPagination } from './PresensiSantriPagination';
import { PresensiSantriSummaryCards } from './PresensiSantriSummaryCards';
import { PresensiSantriTable } from './PresensiSantriTable';

export function PresensiSantriDashboard() {
    const {
        auth,
        attendances,
        filters,
        pagination,
        options,
        errors,
        canManage,
    } = usePage<StudentAttendanceIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [dateFrom, setDateFrom] = useState(
        filters.filter?.date_from ?? '',
    );
    const [dateTo, setDateTo] = useState(filters.filter?.date_to ?? '');
    const [contextType, setContextType] = useState<string>(
        filters.filter?.context_type ?? 'all',
    );
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [mutationAttendance, setMutationAttendance] =
        useState<StudentAttendance | null>(null);
    const [mutationOpen, setMutationOpen] = useState(false);
    const canView = canAccess(auth, 'presensi_santri.view');
    const attendanceIndexUrl = () =>
        routeOr(
            '/pesantrian/student-attendances',
            'pesantrian.student-attendances.index',
        );

    const visitAttendances = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            attendanceIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    date_from: dateFrom || undefined,
                    date_to: dateTo || undefined,
                    context_type:
                        contextType === 'all' ? undefined : contextType,
                    status: status === 'all' ? undefined : status,
                },
                page: nextPage === 1 ? undefined : nextPage,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
                sort: filters.sort ?? '-attendance_date',
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
        visitAttendances();
    };

    const resetFilters = () => {
        setSearch('');
        setDateFrom('');
        setDateTo('');
        setContextType('all');
        setStatus('all');

        router.get(
            attendanceIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <PresensiSantriAccessDenied />;
    }

    return (
        <div className="space-y-5">
            <PresensiSantriSummaryCards
                total={attendances.meta.total}
                attendances={attendances.data}
            />

            <PresensiSantriActionBar
                canManage={canManage}
                onCreate={() => {
                    setMutationAttendance(null);
                    setMutationOpen(true);
                }}
            />

            {errors && Object.keys(errors).length > 0 ? (
                <p role="alert" className="dashboard-message--error text-sm">
                    Filter presensi santri tidak valid. Periksa input dan coba
                    kembali.
                </p>
            ) : null}

            <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                <PresensiSantriFilters
                    search={search}
                    dateFrom={dateFrom}
                    dateTo={dateTo}
                    contextType={contextType}
                    status={status}
                    perPage={attendances.meta.perPage}
                    options={options}
                    onSearchChange={setSearch}
                    onDateFromChange={setDateFrom}
                    onDateToChange={setDateTo}
                    onContextTypeChange={setContextType}
                    onStatusChange={setStatus}
                    onSubmit={submitFilters}
                    onReset={resetFilters}
                />

                {attendances.data.length > 0 ? (
                    <>
                        <PresensiSantriTable attendances={attendances.data} />
                        <PresensiSantriPagination
                            meta={attendances.meta}
                            pagination={pagination}
                            onPageChange={(page) =>
                                visitAttendances(
                                    page,
                                    attendances.meta.perPage,
                                )
                            }
                            onPerPageChange={(perPage) =>
                                visitAttendances(1, perPage)
                            }
                        />
                    </>
                ) : (
                    <PresensiSantriEmptyState />
                )}
            </section>

            <PresensiSantriMutationDialog
                open={mutationOpen}
                attendance={mutationAttendance}
                options={options}
                onOpenChange={setMutationOpen}
            />
        </div>
    );
}
