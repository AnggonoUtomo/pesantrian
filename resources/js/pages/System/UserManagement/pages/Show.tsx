import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Mail, ShieldCheck, UserRound } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import route from '@/lib/route';
import type { Auth } from '@/types/auth';
import type { UserManagementUser } from '../types';

type UserDetailPageProps = {
    auth: Auth;
    user: UserManagementUser;
    errors?: Record<string, string>;
};

export default function Show() {
    const { user, auth, errors } = usePage<UserDetailPageProps>().props;
    const [reason, setReason] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const canView =
        auth.superSystem === true || auth.permissions?.['user.view'] === true;
    const canImpersonate =
        !user.isProtected &&
        auth.impersonation == null &&
        (auth.superSystem === true ||
            auth.permissions?.['user.impersonate'] === true);

    const submitImpersonation = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsSubmitting(true);
        router.post(
            route('system.users.impersonate', user.id),
            { reason },
            { onFinish: () => setIsSubmitting(false) },
        );
    };

    return (
        <>
            <Head title={canView ? user.name : 'Akses terbatas'} />
            <SystemDashboardLayout
                title={canView ? user.name : 'User detail'}
                description="Detail identity user pada area System."
                actions={
                    <Button asChild variant="outline">
                        <Link href={route('system.users.index')}>
                            <ArrowLeft aria-hidden="true" />
                            Kembali
                        </Link>
                    </Button>
                }
            >
                {!canView ? (
                    <section className="dashboard-card dashboard-card--rose rounded-2xl border p-6 text-center">
                        <ShieldCheck className="mx-auto mb-3 size-10 text-rose-500" />
                        <h2 className="text-lg font-semibold">
                            Akses terbatas
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Anda tidak memiliki permission untuk melihat detail
                            user.
                        </p>
                    </section>
                ) : (
                    <section className="dashboard-card dashboard-card--violet rounded-2xl border p-6">
                        <div className="flex items-start gap-4">
                            <div className="dashboard-icon dashboard-accent--violet flex size-12 items-center justify-center rounded-xl">
                                <UserRound
                                    aria-hidden="true"
                                    className="size-6"
                                />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="text-xs tracking-wide text-muted-foreground uppercase">
                                    User identity
                                </p>
                                <h2 className="mt-1 text-xl font-semibold">
                                    {user.name}
                                </h2>
                                <p className="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                    <Mail
                                        aria-hidden="true"
                                        className="size-4"
                                    />
                                    {user.email}
                                </p>
                            </div>
                            <span className="dashboard-badge rounded-full px-3 py-1 text-xs font-medium">
                                {user.status}
                            </span>
                        </div>
                        <dl className="mt-6 grid gap-4 border-t pt-5 sm:grid-cols-2">
                            <div>
                                <dt className="text-xs text-muted-foreground">
                                    Identifier
                                </dt>
                                <dd className="mt-1 font-mono text-sm break-all">
                                    {user.id}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs text-muted-foreground">
                                    Protected
                                </dt>
                                <dd className="mt-1 text-sm">
                                    {user.isProtected ? 'Ya' : 'Tidak'}
                                </dd>
                            </div>
                        </dl>

                        {canImpersonate ? (
                            <form
                                onSubmit={submitImpersonation}
                                className="mt-6 space-y-3 border-t pt-5"
                            >
                                <div>
                                    <label
                                        htmlFor="impersonation-reason"
                                        className="text-sm font-medium"
                                    >
                                        Alasan impersonation
                                    </label>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        Alasan ini dicatat pada event audit.
                                    </p>
                                </div>
                                <Input
                                    id="impersonation-reason"
                                    value={reason}
                                    onChange={(event) =>
                                        setReason(event.target.value)
                                    }
                                    placeholder="Contoh: pemeriksaan tiket support"
                                    minLength={3}
                                    maxLength={500}
                                    required
                                />
                                {errors?.reason ? (
                                    <p
                                        role="alert"
                                        className="text-sm text-rose-500"
                                    >
                                        {errors.reason}
                                    </p>
                                ) : null}
                                <Button type="submit" disabled={isSubmitting}>
                                    {isSubmitting
                                        ? 'Memulai...'
                                        : 'Mulai impersonation'}
                                </Button>
                            </form>
                        ) : null}
                    </section>
                )}
            </SystemDashboardLayout>
        </>
    );
}
