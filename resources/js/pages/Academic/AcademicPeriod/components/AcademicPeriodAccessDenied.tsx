import { ShieldAlert } from 'lucide-react';

export function AcademicPeriodAccessDenied() {
    return (
        <section
            role="alert"
            className="dashboard-card dashboard-card--red rounded-2xl border p-6"
        >
            <div className="flex items-start gap-3">
                <ShieldAlert
                    className="mt-0.5 size-5 shrink-0 text-destructive"
                    aria-hidden="true"
                />
                <div className="space-y-1">
                    <h2 className="text-base font-semibold">
                        Akses periode akademik ditolak
                    </h2>
                    <p className="text-sm text-foreground/70">
                        Anda membutuhkan permission academic_period.view untuk
                        membuka halaman ini.
                    </p>
                </div>
            </div>
        </section>
    );
}
