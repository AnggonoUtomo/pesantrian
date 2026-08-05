import type { PropsWithChildren, ReactNode } from 'react';

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
    return (
        <div className="theme-dashboard-shell mx-auto flex size-full max-w-7xl flex-1 flex-col px-4 py-6 sm:px-6">
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

            <main className="flex-1">{children}</main>

            <footer className="mt-8 border-t pt-4 text-xs text-foreground/75">
                <p>System Dashboard · {new Date().getFullYear()}</p>
            </footer>
        </div>
    );
}
