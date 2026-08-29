import { Head, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import route from '@/lib/route';
import { EmployeeAccessDenied } from '../components/EmployeeAccessDenied';
import { EmployeeEmptyState } from '../components/EmployeeEmptyState';
import { EmployeeFilterForm } from '../components/EmployeeFilterForm';
import { EmployeeList } from '../components/EmployeeList';
import { EmployeePagination } from '../components/EmployeePagination';
import { EmployeeSummary } from '../components/EmployeeSummary';
import type { HumanResourceEmployeePageProps } from '../types';

export default function Index() {
    const { auth, employees, filters, pagination, errors } =
        usePage<HumanResourceEmployeePageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState<string>(
        filters.filter?.status ?? 'all',
    );
    const [employmentType, setEmploymentType] = useState<string>(
        filters.filter?.employment_type ?? 'all',
    );

    const canView = canAccess(auth, 'human_resource.view');
    const activeCount = useMemo(
        () =>
            employees.data.filter((employee) => employee.status === 'active')
                .length,
        [employees.data],
    );

    const visitEmployees = (
        nextPage = 1,
        nextPerPage = Number(filters.per_page ?? pagination.defaultPerPage),
    ) => {
        router.get(
            route('human-resource.employees.index'),
            {
                search: search.trim() || undefined,
                filter: {
                    status: status === 'all' ? undefined : status,
                    employment_type:
                        employmentType === 'all' ? undefined : employmentType,
                },
                page: nextPage === 1 ? undefined : nextPage,
                per_page:
                    nextPerPage === pagination.defaultPerPage
                        ? undefined
                        : nextPerPage,
                sort: filters.sort ?? 'employee_no',
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
        visitEmployees();
    };

    const resetFilters = () => {
        setSearch('');
        setStatus('all');
        setEmploymentType('all');

        router.get(
            route('human-resource.employees.index'),
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
                <Head title="SDM Pesantren" />
                <EmployeeAccessDenied />
            </>
        );
    }

    return (
        <>
            <Head title="SDM Pesantren" />
            <SystemDashboardLayout
                eyebrow="HumanResource"
                title="SDM Pesantren"
                description="Tinjau pegawai, guru, ustadz, musyrif, staff, status, dan penempatan utama SDM pesantren."
            >
                <div className="space-y-5">
                    <EmployeeSummary
                        total={employees.meta.total}
                        active={activeCount}
                        inactive={employees.data.length - activeCount}
                    />

                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Filter employee tidak valid. Periksa input dan coba
                            kembali.
                        </p>
                    ) : null}

                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <EmployeeFilterForm
                            search={search}
                            status={status}
                            employmentType={employmentType}
                            perPage={employees.meta.perPage}
                            onSearchChange={setSearch}
                            onStatusChange={setStatus}
                            onEmploymentTypeChange={setEmploymentType}
                            onSubmit={submitFilters}
                            onReset={resetFilters}
                        />

                        {employees.data.length > 0 ? (
                            <>
                                <EmployeeList employees={employees.data} />
                                <EmployeePagination
                                    meta={employees.meta}
                                    pagination={pagination}
                                    onPageChange={(page) =>
                                        visitEmployees(
                                            page,
                                            employees.meta.perPage,
                                        )
                                    }
                                    onPerPageChange={(perPage) =>
                                        visitEmployees(1, perPage)
                                    }
                                />
                            </>
                        ) : (
                            <EmployeeEmptyState />
                        )}
                    </section>
                </div>
            </SystemDashboardLayout>
        </>
    );
}
