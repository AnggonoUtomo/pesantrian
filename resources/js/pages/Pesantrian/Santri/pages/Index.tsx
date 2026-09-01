import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import { SantriAccessDenied } from '../components/SantriAccessDenied';
import { SantriActionBar } from '../components/SantriActionBar';
import { SantriAdmissionConversionDialog } from '../components/SantriAdmissionConversionDialog';
import { SantriArchiveDialog } from '../components/SantriArchiveDialog';
import { SantriEmptyState } from '../components/SantriEmptyState';
import { SantriFilters } from '../components/SantriFilters';
import { SantriLifecycleDialog } from '../components/SantriLifecycleDialog';
import { SantriMutationDialog } from '../components/SantriMutationDialog';
import { SantriPagination } from '../components/SantriPagination';
import { SantriRestoreDialog } from '../components/SantriRestoreDialog';
import { SantriSummaryCards } from '../components/SantriSummaryCards';
import { SantriTable } from '../components/SantriTable';
import type {
    Student,
    StudentArchiveFilter,
    StudentIndexPageProps,
} from '../types';

export default function Index() {
    const { auth, students, filters, pagination, primaryUnitOptions, errors } =
        usePage<StudentIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [primaryUnitId, setPrimaryUnitId] = useState<string>(
        filters.filter?.primary_unit_id ?? 'all',
    );
    const [archived, setArchived] = useState<StudentArchiveFilter>(
        filters.filter?.archived ?? 'active',
    );
    const [mutationDialogOpen, setMutationDialogOpen] = useState(false);
    const [conversionDialogOpen, setConversionDialogOpen] = useState(false);
    const [lifecycleStudent, setLifecycleStudent] = useState<Student | null>(
        null,
    );
    const [archiveStudent, setArchiveStudent] = useState<Student | null>(null);
    const [restoreStudent, setRestoreStudent] = useState<Student | null>(null);
    const canView = canAccess(auth, 'santri.view');
    const canManage = canAccess(auth, 'santri.manage');
    const canLifecycle = canAccess(auth, 'santri.lifecycle');
    const canArchive = canAccess(auth, 'santri.archive');
    const studentIndexUrl = () =>
        routeOr('/pesantrian/students', 'pesantrian.students.index');

    const visitStudents = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            studentIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    status: status === 'all' ? undefined : status,
                    primary_unit_id:
                        primaryUnitId === 'all' ? undefined : primaryUnitId,
                    archived: archived === 'active' ? undefined : archived,
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
        visitStudents();
    };

    const resetFilters = () => {
        setSearch('');
        setStatus('all');
        setPrimaryUnitId('all');
        setArchived('active');

        router.get(
            studentIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <SantriAccessDenied />;
    }

    return (
        <>
            <Head title="Data Induk Santri" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Data Induk Santri"
                description="Tinjau data induk santri, asal PPDB, unit utama, status lifecycle, dan wali snapshot minimum."
            >
                <div className="space-y-5">
                    <SantriSummaryCards
                        total={students.meta.total}
                        students={students.data}
                    />

                    <SantriActionBar
                        canManage={canManage}
                        onCreate={() => setMutationDialogOpen(true)}
                        onConvert={() => setConversionDialogOpen(true)}
                    />

                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Filter data santri tidak valid. Periksa input dan
                            coba kembali.
                        </p>
                    ) : null}

                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <SantriFilters
                            search={search}
                            status={status}
                            primaryUnitId={primaryUnitId}
                            archived={archived}
                            perPage={students.meta.perPage}
                            primaryUnitOptions={primaryUnitOptions}
                            onSearchChange={setSearch}
                            onStatusChange={setStatus}
                            onPrimaryUnitChange={setPrimaryUnitId}
                            onArchivedChange={setArchived}
                            onSubmit={submitFilters}
                            onReset={resetFilters}
                        />

                        {students.data.length > 0 ? (
                            <>
                                <SantriTable
                                    students={students.data}
                                    primaryUnitOptions={primaryUnitOptions}
                                    archivedView={archived === 'archived'}
                                    canLifecycle={canLifecycle}
                                    canArchive={canArchive}
                                    onLifecycle={setLifecycleStudent}
                                    onArchive={setArchiveStudent}
                                    onRestore={setRestoreStudent}
                                />
                                <SantriPagination
                                    meta={students.meta}
                                    pagination={pagination}
                                    onPageChange={(page) =>
                                        visitStudents(
                                            page,
                                            students.meta.perPage,
                                        )
                                    }
                                    onPerPageChange={(perPage) =>
                                        visitStudents(1, perPage)
                                    }
                                />
                            </>
                        ) : (
                            <SantriEmptyState />
                        )}
                    </section>
                </div>
            </SystemDashboardLayout>

            <SantriMutationDialog
                open={mutationDialogOpen}
                student={null}
                primaryUnitOptions={primaryUnitOptions}
                onOpenChange={setMutationDialogOpen}
            />
            <SantriAdmissionConversionDialog
                open={conversionDialogOpen}
                onOpenChange={setConversionDialogOpen}
            />
            <SantriLifecycleDialog
                open={lifecycleStudent !== null}
                student={lifecycleStudent}
                onOpenChange={(open) => !open && setLifecycleStudent(null)}
            />
            <SantriArchiveDialog
                open={archiveStudent !== null}
                student={archiveStudent}
                onOpenChange={(open) => !open && setArchiveStudent(null)}
            />
            <SantriRestoreDialog
                open={restoreStudent !== null}
                student={restoreStudent}
                onOpenChange={(open) => !open && setRestoreStudent(null)}
            />
        </>
    );
}
