import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import type {
    ClassGroupArchiveFilter,
    ClassGroupIndexPageProps,
} from '../types';
import { KelasRombelAccessDenied } from './KelasRombelAccessDenied';
import { KelasRombelActionBar } from './KelasRombelActionBar';
import { KelasRombelEmptyState } from './KelasRombelEmptyState';
import { KelasRombelFilters } from './KelasRombelFilters';
import { KelasRombelMutationDialogs } from './KelasRombelMutationDialogs';
import { KelasRombelPagination } from './KelasRombelPagination';
import { KelasRombelSummaryCards } from './KelasRombelSummaryCards';
import { KelasRombelTable } from './KelasRombelTable';

export function KelasRombelDashboard() {
    const {
        auth,
        classGroups,
        filters,
        pagination,
        options,
        errors,
        canManage,
    } = usePage<ClassGroupIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [archived, setArchived] = useState<ClassGroupArchiveFilter>(
        filters.filter?.archived ?? 'active',
    );
    const [academicYearId, setAcademicYearId] = useState<string>(
        filters.filter?.academic_year_id ?? 'all',
    );
    const [academicTermId, setAcademicTermId] = useState<string>(
        filters.filter?.academic_term_id ?? 'all',
    );
    const [unitId, setUnitId] = useState<string>(
        filters.filter?.unit_id ?? 'all',
    );
    const [curriculumId, setCurriculumId] = useState<string>(
        filters.filter?.curriculum_id ?? 'all',
    );
    const [curriculumFormOpen, setCurriculumFormOpen] = useState(false);
    const [levelFormOpen, setLevelFormOpen] = useState(false);
    const [classGroupFormOpen, setClassGroupFormOpen] = useState(false);
    const canView = canAccess(auth, 'kelas_rombel.view');
    const classGroupIndexUrl = () =>
        routeOr('/academic/class-groups', 'academic.class-groups.index');

    const visitClassGroups = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            classGroupIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    status: status === 'all' ? undefined : status,
                    archived: archived === 'active' ? undefined : archived,
                    academic_year_id:
                        academicYearId === 'all' ? undefined : academicYearId,
                    academic_term_id:
                        academicTermId === 'all' ? undefined : academicTermId,
                    unit_id: unitId === 'all' ? undefined : unitId,
                    curriculum_id:
                        curriculumId === 'all' ? undefined : curriculumId,
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
        visitClassGroups();
    };

    const resetFilters = () => {
        setSearch('');
        setStatus('all');
        setArchived('active');
        setAcademicYearId('all');
        setAcademicTermId('all');
        setUnitId('all');
        setCurriculumId('all');

        router.get(
            classGroupIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <KelasRombelAccessDenied />;
    }

    return (
        <div className="space-y-5">
            <KelasRombelSummaryCards
                total={classGroups.meta.total}
                classGroups={classGroups.data}
            />
            <KelasRombelActionBar
                canManage={canManage}
                onCreateCurriculum={() => setCurriculumFormOpen(true)}
                onCreateLevel={() => setLevelFormOpen(true)}
                onCreateClassGroup={() => setClassGroupFormOpen(true)}
            />

            {errors && Object.keys(errors).length > 0 ? (
                <p role="alert" className="dashboard-message--error text-sm">
                    Filter atau form rombel tidak valid. Periksa input dan coba
                    kembali.
                </p>
            ) : null}

            <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                <KelasRombelFilters
                    search={search}
                    status={status}
                    archived={archived}
                    academicYearId={academicYearId}
                    academicTermId={academicTermId}
                    unitId={unitId}
                    curriculumId={curriculumId}
                    perPage={classGroups.meta.perPage}
                    options={options}
                    onSearchChange={setSearch}
                    onStatusChange={setStatus}
                    onArchivedChange={setArchived}
                    onAcademicYearChange={setAcademicYearId}
                    onAcademicTermChange={setAcademicTermId}
                    onUnitChange={setUnitId}
                    onCurriculumChange={setCurriculumId}
                    onSubmit={submitFilters}
                    onReset={resetFilters}
                />

                {classGroups.data.length > 0 ? (
                    <>
                        <KelasRombelTable classGroups={classGroups.data} />
                        <KelasRombelPagination
                            meta={classGroups.meta}
                            pagination={pagination}
                            onPageChange={(page) =>
                                visitClassGroups(page, classGroups.meta.perPage)
                            }
                            onPerPageChange={(perPage) =>
                                visitClassGroups(1, perPage)
                            }
                        />
                    </>
                ) : (
                    <KelasRombelEmptyState />
                )}
            </section>

            <KelasRombelMutationDialogs
                options={options}
                curriculumFormOpen={curriculumFormOpen}
                levelFormOpen={levelFormOpen}
                classGroupFormOpen={classGroupFormOpen}
                setCurriculumFormOpen={setCurriculumFormOpen}
                setLevelFormOpen={setLevelFormOpen}
                setClassGroupFormOpen={setClassGroupFormOpen}
            />
        </div>
    );
}
