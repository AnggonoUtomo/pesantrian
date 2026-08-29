import { router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import { canAccess } from '@/lib/authorization';
import route from '@/lib/route';
import type {
    AcademicTerm,
    AcademicPeriodPageProps,
    AcademicPeriodStatus,
    AcademicYear,
} from '../types';
import { AcademicPeriodAccessDenied } from './AcademicPeriodAccessDenied';
import { AcademicPeriodFilterBar } from './AcademicPeriodFilterBar';
import { AcademicPeriodMutationDialogs } from './AcademicPeriodMutationDialogs';
import { AcademicPeriodSummaryCards } from './AcademicPeriodSummaryCards';
import { AcademicTermList } from './AcademicTermList';
import { AcademicYearList } from './AcademicYearList';
import { CurrentAcademicTermCard } from './CurrentAcademicTermCard';

export function AcademicPeriodDashboard() {
    const {
        auth,
        years,
        terms,
        currentTerm,
        filters,
        pagination,
        canManage,
    } = usePage<AcademicPeriodPageProps>().props;
    const [yearSearch, setYearSearch] = useState(filters.year_search ?? '');
    const [termSearch, setTermSearch] = useState(filters.term_search ?? '');
    const [yearStatus, setYearStatus] = useState<AcademicPeriodStatus | 'all'>(
        filters.year_status ?? 'all',
    );
    const [termStatus, setTermStatus] = useState<AcademicPeriodStatus | 'all'>(
        filters.term_status ?? 'all',
    );
    const [yearFormOpen, setYearFormOpen] = useState(false);
    const [termFormOpen, setTermFormOpen] = useState(false);
    const [editingYear, setEditingYear] = useState<AcademicYear | null>(null);
    const [editingTerm, setEditingTerm] = useState<AcademicTerm | null>(null);
    const [activatingTerm, setActivatingTerm] =
        useState<AcademicTerm | null>(null);
    const [closingTerm, setClosingTerm] = useState<AcademicTerm | null>(null);
    const canView = canAccess(auth, 'academic_period.view');
    const activeYearCount = useMemo(
        () => years.data.filter((year) => year.status === 'active').length,
        [years.data],
    );
    const activeTermCount = useMemo(
        () => terms.data.filter((term) => term.is_active).length,
        [terms.data],
    );

    const visitPeriods = (
        next: {
            yearPage?: number;
            termPage?: number;
            perPage?: number;
        } = {},
    ) => {
        const nextPerPage = next.perPage ?? years.meta.perPage;

        router.get(
            route('academic.periods.index'),
            {
                year_search: yearSearch.trim() || undefined,
                term_search: termSearch.trim() || undefined,
                year_status:
                    yearStatus === 'all' ? undefined : yearStatus,
                term_status:
                    termStatus === 'all' ? undefined : termStatus,
                year_page:
                    next.yearPage && next.yearPage > 1
                        ? next.yearPage
                        : undefined,
                term_page:
                    next.termPage && next.termPage > 1
                        ? next.termPage
                        : undefined,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
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
        visitPeriods();
    };

    const resetFilters = () => {
        setYearSearch('');
        setTermSearch('');
        setYearStatus('all');
        setTermStatus('all');

        router.get(
            route('academic.periods.index'),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    if (!canView) {
        return <AcademicPeriodAccessDenied />;
    }

    const openCreateYear = () => {
        setEditingYear(null);
        setYearFormOpen(true);
    };

    const openCreateTerm = () => {
        setEditingTerm(null);
        setTermFormOpen(true);
    };

    const openEditYear = (year: AcademicYear) => {
        setEditingYear(year);
        setYearFormOpen(true);
    };

    const openEditTerm = (term: AcademicTerm) => {
        setEditingTerm(term);
        setTermFormOpen(true);
    };

    return (
        <div className="space-y-5">
            <AcademicPeriodSummaryCards
                totalYears={years.meta.total}
                activeYears={activeYearCount}
                totalTerms={terms.meta.total}
                activeTerms={activeTermCount}
                canManage={canManage}
                onCreateYear={openCreateYear}
                onCreateTerm={openCreateTerm}
            />
            <CurrentAcademicTermCard currentTerm={currentTerm} />
            <AcademicPeriodFilterBar
                yearSearch={yearSearch}
                termSearch={termSearch}
                yearStatus={yearStatus}
                termStatus={termStatus}
                perPage={years.meta.perPage}
                perPageOptions={pagination.perPageOptions}
                onYearSearchChange={setYearSearch}
                onTermSearchChange={setTermSearch}
                onYearStatusChange={setYearStatus}
                onTermStatusChange={setTermStatus}
                onPerPageChange={(perPage) => visitPeriods({ perPage })}
                onSubmit={submitFilters}
                onReset={resetFilters}
            />
            <div className="grid gap-5 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                <AcademicYearList
                    years={years.data}
                    meta={years.meta}
                    canManage={canManage}
                    onPageChange={(page) => visitPeriods({ yearPage: page })}
                    onEdit={openEditYear}
                />
                <AcademicTermList
                    terms={terms.data}
                    meta={terms.meta}
                    canManage={canManage}
                    onPageChange={(page) => visitPeriods({ termPage: page })}
                    onEdit={openEditTerm}
                    onActivate={setActivatingTerm}
                    onClose={setClosingTerm}
                />
            </div>
            <AcademicPeriodMutationDialogs
                years={years.data}
                yearFormOpen={yearFormOpen}
                termFormOpen={termFormOpen}
                editingYear={editingYear}
                editingTerm={editingTerm}
                activatingTerm={activatingTerm}
                closingTerm={closingTerm}
                setYearFormOpen={setYearFormOpen}
                setTermFormOpen={setTermFormOpen}
                setActivatingTerm={setActivatingTerm}
                setClosingTerm={setClosingTerm}
            />
        </div>
    );
}
