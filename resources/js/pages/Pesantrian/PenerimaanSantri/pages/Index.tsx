import { Head, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import route from '@/lib/route';
import { AdmissionAccessDenied } from '../components/AdmissionAccessDenied';
import { AdmissionEmptyState } from '../components/AdmissionEmptyState';
import { AdmissionFilterForm } from '../components/AdmissionFilterForm';
import {
    AdmissionList,
    targetUnitNameMap,
} from '../components/AdmissionList';
import { AdmissionPagination } from '../components/AdmissionPagination';
import { AdmissionSummary } from '../components/AdmissionSummary';
import type { AdmissionPageProps } from '../types';

export default function Index() {
    const {
        auth,
        admissions,
        filters,
        pagination,
        targetUnitOptions,
        errors,
    } = usePage<AdmissionPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [targetUnitId, setTargetUnitId] = useState<string>(
        filters.filter?.target_unit_id ?? 'all',
    );
    const [registrationFeeStatus, setRegistrationFeeStatus] = useState<string>(
        filters.filter?.registration_fee_status ?? 'all',
    );

    const canView = canAccess(auth, 'penerimaan_santri.view');
    const targetUnitNameById = useMemo(
        () => targetUnitNameMap(targetUnitOptions),
        [targetUnitOptions],
    );

    const visitAdmissions = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            route('pesantrian.admissions.index'),
            {
                search: search.trim() || undefined,
                filter: {
                    status: status === 'all' ? undefined : status,
                    target_unit_id:
                        targetUnitId === 'all' ? undefined : targetUnitId,
                    registration_fee_status:
                        registrationFeeStatus === 'all'
                            ? undefined
                            : registrationFeeStatus,
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
        visitAdmissions();
    };

    const resetFilters = () => {
        setSearch('');
        setStatus('all');
        setTargetUnitId('all');
        setRegistrationFeeStatus('all');

        router.get(
            route('pesantrian.admissions.index'),
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
                <Head title="PPDB / Penerimaan Santri" />
                <AdmissionAccessDenied />
            </>
        );
    }

    return (
        <>
            <Head title="PPDB / Penerimaan Santri" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="PPDB / Penerimaan Santri"
                description="Tinjau calon santri, wali, status administrasi biaya, checklist dokumen, dan keputusan awal penerimaan."
            >
                <div className="space-y-5">
                    <AdmissionSummary
                        total={admissions.meta.total}
                        admissions={admissions.data}
                    />

                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Filter penerimaan santri tidak valid. Periksa input
                            dan coba kembali.
                        </p>
                    ) : null}

                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <AdmissionFilterForm
                            search={search}
                            status={status}
                            targetUnitId={targetUnitId}
                            registrationFeeStatus={registrationFeeStatus}
                            perPage={admissions.meta.perPage}
                            targetUnitOptions={targetUnitOptions}
                            onSearchChange={setSearch}
                            onStatusChange={setStatus}
                            onTargetUnitChange={setTargetUnitId}
                            onRegistrationFeeStatusChange={
                                setRegistrationFeeStatus
                            }
                            onSubmit={submitFilters}
                            onReset={resetFilters}
                        />

                        {admissions.data.length > 0 ? (
                            <>
                                <AdmissionList
                                    admissions={admissions.data}
                                    targetUnitNameById={targetUnitNameById}
                                />
                                <AdmissionPagination
                                    meta={admissions.meta}
                                    pagination={pagination}
                                    onPageChange={(page) =>
                                        visitAdmissions(
                                            page,
                                            admissions.meta.perPage,
                                        )
                                    }
                                    onPerPageChange={(perPage) =>
                                        visitAdmissions(1, perPage)
                                    }
                                />
                            </>
                        ) : (
                            <AdmissionEmptyState />
                        )}
                    </section>
                </div>
            </SystemDashboardLayout>
        </>
    );
}
