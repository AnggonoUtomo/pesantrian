import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { canAccess } from '@/lib/authorization';
import { routeOr } from '@/lib/route';
import type {
    StudentAchievement,
    StudentAchievementCategory,
    StudentAchievementIndexPageProps,
} from '../types';
import { PrestasiSantriAccessDenied } from './PrestasiSantriAccessDenied';
import { PrestasiSantriActionBar } from './PrestasiSantriActionBar';
import { PrestasiSantriCategoryDialog } from './PrestasiSantriCategoryDialog';
import { PrestasiSantriCategoryPanel } from './PrestasiSantriCategoryPanel';
import { PrestasiSantriEmptyState } from './PrestasiSantriEmptyState';
import { PrestasiSantriFilters } from './PrestasiSantriFilters';
import {
    ArchivePrestasiCategoryDialog,
    RequestRevisionPrestasiDialog,
    SubmitPrestasiDialog,
    VerifyPrestasiDialog,
    VoidPrestasiDialog,
} from './PrestasiSantriLifecycleDialogs';
import { PrestasiSantriMutationDialog } from './PrestasiSantriMutationDialog';
import { PrestasiSantriPagination } from './PrestasiSantriPagination';
import { PrestasiSantriSummaryCards } from './PrestasiSantriSummaryCards';
import { PrestasiSantriTable } from './PrestasiSantriTable';

export function PrestasiSantriDashboard() {
    const {
        auth,
        achievements,
        filters,
        pagination,
        options,
        errors,
        canManage,
        canRecord,
        canVerify,
        canArchive,
    } = usePage<StudentAchievementIndexPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [dateFrom, setDateFrom] = useState(
        filters.filter?.date_from ?? '',
    );
    const [dateTo, setDateTo] = useState(filters.filter?.date_to ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [level, setLevel] = useState<string>(
        filters.filter?.level ?? 'all',
    );
    const [categoryId, setCategoryId] = useState<string>(
        filters.filter?.category_id ?? 'all',
    );
    const [mutationAchievement, setMutationAchievement] =
        useState<StudentAchievement | null>(null);
    const [mutationDialogOpen, setMutationDialogOpen] = useState(false);
    const [categoryDialogOpen, setCategoryDialogOpen] = useState(false);
    const [categoryMutation, setCategoryMutation] =
        useState<StudentAchievementCategory | null>(null);
    const [categoryArchive, setCategoryArchive] =
        useState<StudentAchievementCategory | null>(null);
    const [submitAchievement, setSubmitAchievement] =
        useState<StudentAchievement | null>(null);
    const [verifyAchievement, setVerifyAchievement] =
        useState<StudentAchievement | null>(null);
    const [revisionAchievement, setRevisionAchievement] =
        useState<StudentAchievement | null>(null);
    const [voidAchievement, setVoidAchievement] =
        useState<StudentAchievement | null>(null);
    const canView = canAccess(auth, 'prestasi_santri.view');
    const achievementIndexUrl = () =>
        routeOr(
            '/pesantrian/prestasi-santri',
            'pesantrian.prestasi-santri.index',
        );

    const visitAchievements = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            achievementIndexUrl(),
            {
                search: search.trim() || undefined,
                filter: {
                    date_from: dateFrom || undefined,
                    date_to: dateTo || undefined,
                    status: status === 'all' ? undefined : status,
                    level: level === 'all' ? undefined : level,
                    category_id:
                        categoryId === 'all' ? undefined : categoryId,
                },
                page: nextPage === 1 ? undefined : nextPage,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
                sort: filters.sort ?? '-achieved_on',
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
        visitAchievements();
    };

    const resetFilters = () => {
        setSearch('');
        setDateFrom('');
        setDateTo('');
        setStatus('all');
        setLevel('all');
        setCategoryId('all');

        router.get(
            achievementIndexUrl(),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const openCreateDialog = () => {
        setMutationAchievement(null);
        setMutationDialogOpen(true);
    };

    const openEditDialog = (achievement: StudentAchievement) => {
        setMutationAchievement(achievement);
        setMutationDialogOpen(true);
    };

    const openCreateCategoryDialog = () => {
        setCategoryMutation(null);
        setCategoryDialogOpen(true);
    };

    if (!canView) {
        return <PrestasiSantriAccessDenied />;
    }

    return (
        <div className="space-y-5">
            <PrestasiSantriActionBar
                canManage={canManage}
                canRecord={canRecord}
                canVerify={canVerify}
                canArchive={canArchive}
                onCreate={openCreateDialog}
                onCreateCategory={openCreateCategoryDialog}
            />

            <PrestasiSantriSummaryCards
                total={achievements.meta.total}
                achievements={achievements.data}
            />

            <PrestasiSantriCategoryPanel
                categories={options.categories}
                canManage={canManage}
                onEdit={(category) => {
                    setCategoryMutation(category);
                    setCategoryDialogOpen(true);
                }}
                onArchive={setCategoryArchive}
            />

            {errors && Object.keys(errors).length > 0 ? (
                <p role="alert" className="dashboard-message--error text-sm">
                    Filter prestasi santri tidak valid. Periksa input dan coba
                    kembali.
                </p>
            ) : null}

            <section className="dashboard-card dashboard-card--yellow space-y-4 rounded-2xl border p-4 sm:p-5">
                <PrestasiSantriFilters
                    search={search}
                    dateFrom={dateFrom}
                    dateTo={dateTo}
                    status={status}
                    level={level}
                    categoryId={categoryId}
                    perPage={achievements.meta.perPage}
                    options={options}
                    onSearchChange={setSearch}
                    onDateFromChange={setDateFrom}
                    onDateToChange={setDateTo}
                    onStatusChange={setStatus}
                    onLevelChange={setLevel}
                    onCategoryChange={setCategoryId}
                    onSubmit={submitFilters}
                    onReset={resetFilters}
                />

                {achievements.data.length > 0 ? (
                    <>
                        <PrestasiSantriTable
                            achievements={achievements.data}
                            canRecord={canRecord}
                            canVerify={canVerify}
                            canArchive={canArchive}
                            onEdit={openEditDialog}
                            onSubmitAchievement={setSubmitAchievement}
                            onVerify={setVerifyAchievement}
                            onRequestRevision={setRevisionAchievement}
                            onVoid={setVoidAchievement}
                        />
                        <PrestasiSantriPagination
                            meta={achievements.meta}
                            pagination={pagination}
                            onPageChange={(page) =>
                                visitAchievements(
                                    page,
                                    achievements.meta.perPage,
                                )
                            }
                            onPerPageChange={(perPage) =>
                                visitAchievements(1, perPage)
                            }
                        />
                    </>
                ) : (
                    <PrestasiSantriEmptyState />
                )}
            </section>
            <PrestasiSantriMutationDialog
                open={mutationDialogOpen}
                achievement={mutationAchievement}
                options={options}
                onOpenChange={setMutationDialogOpen}
            />
            <PrestasiSantriCategoryDialog
                open={categoryDialogOpen}
                category={categoryMutation}
                onOpenChange={setCategoryDialogOpen}
            />
            {categoryArchive && canManage ? (
                <ArchivePrestasiCategoryDialog
                    open={categoryArchive !== null}
                    category={categoryArchive}
                    onOpenChange={(open) =>
                        !open && setCategoryArchive(null)
                    }
                />
            ) : null}
            {submitAchievement ? (
                <SubmitPrestasiDialog
                    open={submitAchievement !== null}
                    achievement={submitAchievement}
                    onOpenChange={(open) =>
                        !open && setSubmitAchievement(null)
                    }
                />
            ) : null}
            {verifyAchievement ? (
                <VerifyPrestasiDialog
                    open={verifyAchievement !== null}
                    achievement={verifyAchievement}
                    onOpenChange={(open) =>
                        !open && setVerifyAchievement(null)
                    }
                />
            ) : null}
            {revisionAchievement ? (
                <RequestRevisionPrestasiDialog
                    open={revisionAchievement !== null}
                    achievement={revisionAchievement}
                    onOpenChange={(open) =>
                        !open && setRevisionAchievement(null)
                    }
                />
            ) : null}
            {voidAchievement ? (
                <VoidPrestasiDialog
                    open={voidAchievement !== null}
                    achievement={voidAchievement}
                    onOpenChange={(open) => !open && setVoidAchievement(null)}
                />
            ) : null}
        </div>
    );
}
