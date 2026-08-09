import { router, usePage } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import type { PropsWithChildren, ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import route from '@/lib/route';
import type { Auth } from '@/types/auth';

interface SystemDashboardLayoutProps extends PropsWithChildren {
    eyebrow?: string;
    title: string;
    description: string;
    actions?: ReactNode;
}

export default function SystemDashboardLayout({
    eyebrow = 'System dashboard',
    title,
    description,
    actions,
    children,
}: SystemDashboardLayoutProps) {
    const { auth } = usePage<{ auth: Auth }>().props;

    const leaveImpersonation = () => {
        router.post(route('system.users.impersonation.leave'));
    };

    return (
        <div className="theme-dashboard-shell mx-auto flex size-full max-w-7xl min-w-0 flex-1 flex-col px-4 py-6 sm:px-6">
            {auth.impersonation ? (
                <div className="mb-5 flex flex-col gap-3 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-start gap-3">
                        <ShieldAlert
                            className="mt-0.5 size-5 shrink-0 text-amber-500"
                            aria-hidden="true"
                        />
                        <div>
                            <p className="font-medium">
                                Mode impersonation aktif
                            </p>
                            <p className="text-foreground/75">
                                Anda sedang melihat aplikasi sebagai user
                                target.
                            </p>
                        </div>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={leaveImpersonation}
                    >
                        Kembali ke akun asli
                    </Button>
                </div>
            ) : null}

            <header className="dashboard-section-header mb-6 flex flex-col gap-4 border-b border-border/70 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div className="space-y-1">
                    <p className="text-xs font-medium tracking-wide text-primary uppercase">
                        {eyebrow}
                    </p>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {title}
                    </h1>
                    <p className="max-w-2xl text-sm text-foreground/75">
                        {description}
                    </p>
                </div>
                {actions ? (
                    <div className="flex items-center gap-2">{actions}</div>
                ) : null}
            </header>

            <main className="min-w-0 flex-1">{children}</main>

            <footer className="mt-8 border-t border-border/70 pt-4 text-xs text-foreground/60">
                <p>Created by Ino@2026</p>
            </footer>
        </div>
    );
}
