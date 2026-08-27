import { ShieldCheck } from 'lucide-react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';

export function OrganizationUnitAccessDenied() {
    return (
        <SystemDashboardLayout
            eyebrow="Organization"
            title="Unit Organisasi"
            description="Kelola struktur yayasan dan unit operasional pesantren."
        >
            <section className="dashboard-card dashboard-card--rose rounded-2xl border p-8 text-center">
                <ShieldCheck className="mx-auto size-10 text-rose-500" />
                <h2 className="mt-3 text-lg font-semibold">
                    Akses terbatas
                </h2>
                <p className="mt-2 text-sm text-foreground/65">
                    Akun ini tidak memiliki permission untuk melihat unit
                    organisasi.
                </p>
            </section>
        </SystemDashboardLayout>
    );
}
