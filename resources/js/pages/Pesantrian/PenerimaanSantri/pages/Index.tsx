import { Head, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import { AdmissionAccessDenied } from '../components/AdmissionAccessDenied';
import { AdmissionDetailDialog } from '../components/AdmissionDetailDialog';
import { AdmissionEmptyState } from '../components/AdmissionEmptyState';
import { AdmissionFilterForm } from '../components/AdmissionFilterForm';
import {
    AdmissionList,
    targetUnitNameMap,
} from '../components/AdmissionList';
import { AdmissionMutationDialog } from '../components/AdmissionMutationDialog';
import { AdmissionPagination } from '../components/AdmissionPagination';
import { AdmissionSummary } from '../components/AdmissionSummary';
import type { AdmissionPageProps, StudentAdmission } from '../types';

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
    const [mutationDialogOpen, setMutationDialogOpen] = useState(false);
    const [detailDialogOpen, setDetailDialogOpen] = useState(false);
    const [selectedAdmission, setSelectedAdmission] =
        useState<StudentAdmission | null>(null);
    const [selectedDetailAdmission, setSelectedDetailAdmission] =
        useState<StudentAdmission | null>(null);

    const canView = canAccess(auth, 'penerimaan_santri.view');
    const canManage = canAccess(auth, 'penerimaan_santri.manage');
    const canDecide = canAccess(auth, 'penerimaan_santri.decide');
    const targetUnitNameById = useMemo(
        () => targetUnitNameMap(targetUnitOptions),
        [targetUnitOptions],
    );
    const admissionIndexUrl = () =>
        routeOr('/pesantrian/admissions', 'pesantrian.admissions.index');

    const visitAdmissions = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            admissionIndexUrl(),
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
            admissionIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const createAdmission = () => {
        setSelectedAdmission(null);
        setMutationDialogOpen(true);
    };

    const viewAdmission = (admission: StudentAdmission) => {
        setSelectedDetailAdmission(admission);
        setDetailDialogOpen(true);
    };

    const editAdmission = (admission: StudentAdmission) => {
        setSelectedAdmission(admission);
        setMutationDialogOpen(true);
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
                    {canManage ? (
                        <div className="flex justify-end">
                            <Button
                                type="button"
                                onClick={createAdmission}
                            >
                                Tambah pendaftaran
                            </Button>
                        </div>
                    ) : null}

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
                                    canManage={canManage}
                                    canDecide={canDecide}
                                    onView={viewAdmission}
                                    onEdit={editAdmission}
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
                {canManage ? (
                    <AdmissionMutationDialog
                        key={`${selectedAdmission?.id ?? 'create'}-${mutationDialogOpen ? 'open' : 'closed'}`}
                        open={mutationDialogOpen}
                        admission={selectedAdmission}
                        targetUnitOptions={targetUnitOptions}
                        onOpenChange={setMutationDialogOpen}
                    />
                ) : null}
                <AdmissionDetailDialog
                    open={detailDialogOpen}
                    admission={selectedDetailAdmission}
                    targetUnitNameById={targetUnitNameById}
                    onOpenChange={setDetailDialogOpen}
                />
            </SystemDashboardLayout>
        </>
    );
}
