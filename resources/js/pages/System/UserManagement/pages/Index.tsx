import { Head, Link, router, usePage } from '@inertiajs/react';
import { Search, ShieldCheck, UsersRound } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import route from '@/lib/route';
import type { UserManagementPageProps, UserManagementUser } from '../types';

function statusLabel(status: UserManagementUser['status']): string {
    return {
        active: 'Aktif',
        inactive: 'Tidak aktif',
        suspended: 'Ditangguhkan',
    }[status];
}

export default function Index() {
    const { auth, users, filters, errors } =
        usePage<UserManagementPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [isLoading, setIsLoading] = useState(false);
    const canView =
        auth.superSystem === true || auth.permissions?.['user.view'] === true;

    const submitSearch = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsLoading(true);
        router.get(
            route('system.users.index'),
            { search: search || undefined },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => setIsLoading(false),
            },
        );
    };

    if (!canView) {
        return (
            <>
                <Head title="User Management" />
                <SystemDashboardLayout
                    title="User Management"
                    description="Kelola identity user pada area System."
                >
                    <section className="dashboard-card dashboard-card--rose rounded-2xl border p-6 text-center">
                        <ShieldCheck className="mx-auto mb-3 size-10 text-rose-500" />
                        <h2 className="text-lg font-semibold">
                            Akses terbatas
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Anda tidak memiliki permission untuk melihat daftar
                            user.
                        </p>
                    </section>
                </SystemDashboardLayout>
            </>
        );
    }

    return (
        <>
            <Head title="User Management" />
            <SystemDashboardLayout
                title="User Management"
                description="Tinjau identity, status, dan akses user pada area System."
            >
                <div className="space-y-5">
                    <section className="dashboard-card dashboard-card--blue rounded-2xl border p-5">
                        <div className="mb-4 flex items-center gap-3">
                            <div className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                                <UsersRound
                                    aria-hidden="true"
                                    className="size-5"
                                />
                            </div>
                            <div>
                                <h2 className="font-semibold">Daftar user</h2>
                                <p className="text-sm text-muted-foreground">
                                    {users.length} user ditemukan
                                </p>
                            </div>
                        </div>

                        <form
                            onSubmit={submitSearch}
                            className="flex flex-col gap-2 sm:flex-row"
                            role="search"
                        >
                            <label htmlFor="user-search" className="sr-only">
                                Cari user
                            </label>
                            <div className="relative flex-1">
                                <Search
                                    aria-hidden="true"
                                    className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="user-search"
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Cari nama atau email..."
                                    className="pl-9"
                                />
                            </div>
                            <Button
                                type="submit"
                                variant="outline"
                                disabled={isLoading}
                            >
                                {isLoading ? 'Memuat...' : 'Cari'}
                            </Button>
                        </form>
                    </section>

                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Data user tidak dapat dimuat. Silakan coba lagi.
                        </p>
                    ) : null}

                    {users.length === 0 ? (
                        <section className="dashboard-subcard rounded-2xl border border-dashed p-10 text-center">
                            <UsersRound className="mx-auto mb-3 size-9 text-muted-foreground" />
                            <h2 className="font-semibold">Belum ada user</h2>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Tidak ada user yang cocok dengan pencarian saat
                                ini.
                            </p>
                        </section>
                    ) : (
                        <section className="dashboard-card dashboard-card--cyan overflow-hidden rounded-2xl border">
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[640px] text-left text-sm">
                                    <thead className="border-b bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-5 py-3 font-medium">
                                                User
                                            </th>
                                            <th className="px-5 py-3 font-medium">
                                                Status
                                            </th>
                                            <th className="px-5 py-3 text-right font-medium">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/70">
                                        {users.map((user) => (
                                            <tr
                                                key={user.id}
                                                className="transition-colors hover:bg-accent/40"
                                            >
                                                <td className="px-5 py-4">
                                                    <div className="font-medium">
                                                        {user.name}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {user.email}
                                                    </div>
                                                </td>
                                                <td className="px-5 py-4">
                                                    <span className="dashboard-badge rounded-full px-2.5 py-1 text-xs font-medium">
                                                        {statusLabel(
                                                            user.status,
                                                        )}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-4 text-right">
                                                    <Button
                                                        asChild
                                                        size="sm"
                                                        variant="outline"
                                                    >
                                                        <Link
                                                            href={route(
                                                                'system.users.show',
                                                                user.id,
                                                            )}
                                                        >
                                                            Lihat detail
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    )}
                </div>
            </SystemDashboardLayout>
        </>
    );
}
